require 'net/http'
require 'uri'

class LobbyingScanJob < ApplicationJob
  queue_as :default

  HISTORY_DAYS = 120
  SEARCH_URL = "https://lobbyist.ottawa.ca/search/searchlobbyist.aspx?lang=en"

  def perform(date: nil)
    if date.nil?
      # (-5500..-2400).each { |i| LobbyingScanJob.perform_later(date: Date.today + i.days) }
      (-HISTORY_DAYS..0).each { |i| LobbyingScanJob.set(wait: rand(0..3600).seconds).perform_later(date: Date.today + i.days) }
    else
      lobbying_for_date(date.to_date).each do |e|
        perform_entry(e)
      end
    end
    nil
  end

  def perform_entry(e)
    attr = {
      subject: e["subjects"],
      issue: e["issue"],
      lobbyist_name: e["name"],
      lobbyist_position: e["position"],
      lobbyist_reg_type: e["reg_type"],
      client: e["client"],
      client_org: e["client_org"],
    }
    LobbyingUndertaking.transaction do
      undertaking = LobbyingUndertaking.where(attr).first || LobbyingUndertaking.new(**attr, view_details: e[:params].to_json)
      announcements = []
      unless undertaking.persisted?
        undertaking.save!
        announcements << Announcement.new(message: "New Lobbying undertaking")
      end
      e[:activities].each do |a|
        attr = {
          activity_date: a[:date],
          activity_type: a[:type],
          lobbied_name: a[:lobbied_name],
          lobbied_title: a[:lobbied_title],
        }
        activity = undertaking.activities.where(attr).first || undertaking.activities.new(attr)
        unless activity.persisted?
          activity.save!
          announcements << Announcement.new(message: "Additional Lobbying activity")
        end
      end
      if announcements.any?
        undertaking.announcements << announcements.first
      end
    end
  end

  private

  def lobbying_for_date(date)
    doc = Nokogiri::HTML(search_results_for_date(date))
    doc.xpath('//input').select{|i| i.attributes['name'].value.match(/btnView$/)}.map do |i|
      btn_view = i.attributes['name'].value
      params = {
        '__VIEWSTATE' => view_state(doc),
        '__EVENTVALIDATION' => event_validation(doc),
        btn_view => 'View'
      }
      html = lobbying_entry(params)
      entry = Nokogiri::HTML(html)

      details = {
        params: params
      }

      fields = %w(
        ctl00_MainContent_lblName
        ctl00_MainContent_lblPosition
        ctl00_MainContent_lblRegType

        ctl00_MainContent_lblOrg
        ctl00_MainContent_lblAddr
        ctl00_MainContent_lblCity
        ctl00_MainContent_lblProv
        ctl00_MainContent_lblPC
        ctl00_MainContent_lblStatus

        ctl00_MainContent_lblClient
        ctl00_MainContent_lblClientOrg
        ctl00_MainContent_lblClientAddr
        ctl00_MainContent_lblClientCity
        ctl00_MainContent_lblClientProv
        ctl00_MainContent_lblClientPC

        ctl00_MainContent_lblSubjects
        ctl00_MainContent_lblUndertakingStatus
        ctl00_MainContent_lblIssue
      ).each do |f|
        key = f.gsub(/ctl00_MainContent_lbl/, '').underscore
        details[key] = if entry.xpath("//*[@id=\"#{f}\"]").first
          entry.xpath("//*[@id=\"#{f}\"]").first.children.to_s.gsub('<br>', ' ').gsub(/  */, ' ').gsub(/  *$/, '')
        end
      end

      activities = entry.xpath('//*[@id="ctl00_MainContent_gvActivity"]/tr')
      activities.shift # burn header
      details[:activities] = activities.map do |a|
         CGI.unescapeHTML(a.xpath('td')[2].children.to_s).split("<br>").map do |p|
          i = p.index(" : ")
          name = p[0..(i-1)]
          title = p[(i+3)..]
          {
            date: a.xpath('td')[0].children.to_s.to_date,
            type: a.xpath('td')[1].children.to_s,
            lobbied_name: name,
            lobbied_title: title
          }
        end
      end.flatten
      details
    end.flatten
  end

  def search_results_for_date(date)
    formatted_date = date.strftime("%d-%b-%Y")
  body = "ctl00_scriptManager_HiddenField=%3B%3BAjaxControlToolkit%2C+Version%3D3.5.51116.0%2C+Culture%3Dneutral%2C+PublicKeyToken%3D28f01b0e84b6d53e%3Aen-CA%3A2a06c7e2-728e-4b15-83d6-9b269fb7261e%3Ade1feab2%3Af9cec9bc%3A35576c48%3Af2c8e708%3A720a52bf%3A589eaa30%3A698129cf%3A7a92f56c%3Afcf0e993%3Afb9b4c57%3Accb96cf9&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUJNDA1MTkxNTA1DxYSHhhNYWluU2VhcmNoX1N1YmplY3RNYXR0ZXJkHg9NYWluU2VhcmNoX1dhcmRkHhNNYWluU2VhcmNoX0xvYmJ5aXN0ZR4RTWFpbnNTZWFyY2hfQ291bnQCAh4STWFpblNlYXJjaF9LZXl3b3JkZR4ZTWFpblNlYXJjaF9QZXJzb25zTG9iYmllZGUeEU1haW5TZWFyY2hfVG9EYXRlBgAALGMXHNoIHgtDdXJyZW50UGFnZQIBHhNNYWluU2VhcmNoX0Zyb21EYXRlBgAALGMXHNoIFgJmD2QWBGYPZBYCAgEPFQUmaHR0cHM6Ly9sb2JieWlzdC5vdHRhd2EuY2EvYWNjb3VudC9jZG4maHR0cHM6Ly9sb2JieWlzdC5vdHRhd2EuY2EvYWNjb3VudC9jZG4maHR0cHM6Ly9sb2JieWlzdC5vdHRhd2EuY2EvYWNjb3VudC9jZG4maHR0cHM6Ly9sb2JieWlzdC5vdHRhd2EuY2EvYWNjb3VudC9jZG4maHR0cHM6Ly9sb2JieWlzdC5vdHRhd2EuY2EvYWNjb3VudC9jZG5kAggPZBYCAgMPZBYMAgsPZBYCAgEPZBYEZg9kFgJmD2QWBAITDxBkEBUqEkFmZm9yZGFibGUgSG91c2luZxlBZ3JpY3VsdHVyZS9SdXJhbCBBZmZhaXJzDEFydHMvQ3VsdHVyZRNBdHRyYWN0aW9ucy9Ub3VyaXNtBkJ1ZGdldBFCeS1sYXcvUmVndWxhdGlvbglDaGlsZGNhcmUURWNvbm9taWMgRGV2ZWxvcG1lbnQLRW52aXJvbm1lbnQSRmluYW5jaWFsIFNlcnZpY2VzEUdhcmJhZ2UvUmVjeWNsaW5nDkdyYW50cy9GdW5kaW5nD0hlYWx0aCAmIFNhZmV0eRZJbmZvcm1hdGlvbiBUZWNobm9sb2d5DkluZnJhc3RydWN0dXJlDyAtIENvbnN0cnVjdGlvbhBMaWNlbmNlcy9QZXJtaXRzB1BhcmtpbmcQUGFya3MvUmVjcmVhdGlvbhhQbGFubmluZyBhbmQgRGV2ZWxvcG1lbnQSIC0gUGxhbm5pbmcgUG9saWN5GyAtIEVudmlyb25tZW50YWwgQXNzZXNzbWVudAsgLSBIZXJpdGFnZRAgLSBPZmZpY2lhbCBQbGFuECAtIFpvbmluZyBCeS1sYXcWIC0gUGxhbiBvZiBTdWJkaXZpc2lvbhYgLSBQbGFuIG9mIENvbmRvbWluaXVtDCAtIFNpdGUgUGxhbhEgLSBNaW5vciBWYXJpYW5jZRMgLSBDb25zZW50IHRvIFNldmVyCyAtIFBhcnQgTG90FyAtIENvbWJpbmVkIEFwcGxpY2F0aW9uC1Byb2N1cmVtZW50FFB1YmxpYyBTZWN0b3IgVW5pb24gFFJlYWwgRXN0YXRlL1Byb3BlcnR5BVNpZ25zD1NvY2lhbCBTZXJ2aWNlcxEgLSBMb25nLXRlcm0gQ2FyZQpUYXggUG9saWN5ElRyYW5zaXQvT0MgVHJhbnNwbw5UcmFuc3BvcnRhdGlvbgtXYXRlci9TZXdlchUqIDFCREUwNzkxOTlGODk0NDZCRDc5MkNEOUU1Q0MzRDkyIDIxNDA2MDk3NzY3RDM3NDA4OTVDNEU0MTMwMzAzMzNBIEJFOTVBNjA4QzNCNzEwNEM5MEU1OUQ4RUVGODI2QUU4IEVGQUQ2OTJDMjNFQzhCNEVCNkZGMTI4RjBBMUQwQ0MxIDBDMUI1QkZCNjFGNDg3NEU4N0M1MDg4RDI5QzFDNDQ5IEJCQjI5NTNDOUY3NzNENDNCODkzQUE0NjBFNDhCNkRCIDAwQzE3QkE4RUVDMUExNEVBRUIwMDE5OTNBOTAxQTBGIDVFREU5MkE5Q0Y3RUEwNEZCQUE0MjVDNTkyMzJFRkI5IEQ0NUNFMUJERUIyMTY5NDM5OUE0MDdBRThGMTMxMkMyIDQxOTgwQjQyMTJCRjUwNDJBMjM5RkMyRjI4RUJFNDA4IEJERjhGQjNFOTRCMUY0NDU4NkRFQzFEMDM0RjFFNUYzIDJCOTkwNEFGMDdDNEVCNEY4MEMwNTIzRTUzMjlGQTk2IDFDMkRFRjE4REQyMDQ4NEVCMjNERTdBNkI1Qjg4NzBDIEE1RDcyNTNDODA0RDMxNEM5OTBBRjAwRjBFQUNENDYzIDdFNDlFNEQyNzE1QjQ3NDA5Mjc4RDFEQTA2NjRGRTlEIDAxRkZDMDhFMUJDM0E5NDhBQkM4RjEyMUMyRUQ3NzdBIDA4NTIyRDBCOTQzRDMyNEFBOEM0MjAyNkZBNzU1MUZFIEY0MUY0QTRERTIyRTE1NDQ4REM2QzdBN0M4M0Q4N0E3IDE2RDZBQTA3RDU4RTVGNDA4ODVFMjU0MUE3RDc3MkQ4IDBFREE5RUZERDNBQTE1NDRCRDA4Rjc4MjA4MThENTFGIEI4M0U3QzkyMzNCQjhENDI5RjZDRUZFQ0U3NUY5MjhFIDNBNzkxMENGQzI1NDlFNDQ5N0M4NDhCQjNFMDkwQ0E3IDZCMEJFQTZEQjkzQTg4NDZBMEFDNDJDNEQ2QTZFMkY1IEExOTk2MUQyRDc1NTRDNDhBQTgyNDM0MENGNkU1MkNGIDNEM0RENDMyQzczQkI1NEM4ODM5NTM1ODYxMkU1RTNDIDE1MDVFRjg5QzRERTE0NEI5NkE0NUU0MjA4RDVEQTg1IDQ3RTk3QUQyRUZFQTdFNDc4OEU1RjQ1MjY1NzY0NDIwIDBCNjM1RjdBQTQwQTE5NDFBQTMyOEVBMTc5NTg5OTM5IEYzQUEzQjQzMDRFRDQ1NDBBQTE2Q0ExRDk5Q0VBQzkxIDdBNjg1QkY2QzBCMTNBNDI5M0ExRkJDQzA4MUQ2MUI3IDdCOUM1RTUwRjZFRjU1NEFCMjQwRjQ2RjMzNjZENjQ2IDI2QjM2REI1MEM4OTMwNDg5Q0VFM0EzQUY2NzVGOUM3IDg2REU2RDRGQzkwQjI0NEVCMzBGM0M3NTVCQTI4MTg5IENCODgyRjNBNkVDRkEyNEQ5OTkyMjA1OUM3RDcyNDA1IDdDRkE0REEyMjkyOTNBNDhBMkQ0NDI4OTRBQUJFRjI1IDUzQUE4NjhGMEVEOTY5NDE4RUQ0RjdFNzZCOEI5RUJFIDkzRTk4M0FCRDU4NzkxNDY5NjUwMEMxQUY3MUNCMDg4IDYzNDMyMDFDMEFENzFCNDJBRTU5QTVGMzUyODlCQkVEIDExNTlDNkJGMTgwNDhGNDZBMTE2MkQ2QUVBM0IzODZGIEQ4RTYxQ0M3NTNGMjQwNEZCMEEyQkRCNEE3NDJGN0EzIDZCRUYyOUE0MjQ3OTA5NDE4NDY2QkM3RUIzMjVDRDc1IERGREUzQzUyNkM4NkU3NDQ5NzI2RUFFRDY5QjAwM0QwFCsDKmdnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2RkAhcPEGQQFRgOQ1cgLSBDaXR5IFdpZGUOMSAgLSBPcmzDqWFucyALMiAgLSBJbm5lcyAPMyAgLSBCYXJyaGF2ZW4gETQgIC0gS2FuYXRhIE5vcnRoGDUgIC0gV2VzdCBDYXJsZXRvbi1NYXJjaBA2ICAtIFN0aXR0c3ZpbGxlCDcgIC0gQmF5DDggIC0gQ29sbGVnZRY5ICAtIEtub3hkYWxlLU1lcml2YWxlGTEwIC0gR2xvdWNlc3Rlci1Tb3V0aGdhdGUZMTEgLSBCZWFjb24gSGlsbC1DeXJ2aWxsZRIxMiAtIFJpZGVhdS1WYW5pZXIWMTMgLSBSaWRlYXUtUm9ja2NsaWZmZQ0xNCAtIFNvbWVyc2V0EjE1IC0gS2l0Y2hpc3NpcHBpIAsxNiAtIFJpdmVyIAwxNyAtIENhcGl0YWwPMTggLSBBbHRhIFZpc3RhDzE5IC0gQ3VtYmVybGFuZAwyMCAtIE9zZ29vZGUVMjEgLSBSaWRlYXUtR291bGJvdXJuHDIyIC0gR2xvdWNlc3Rlci1Tb3V0aCBOZXBlYW4RMjMgLSBLYW5hdGEgU291dGgVGCA4MjM3Q0QzNDA1OUYyQzREQUJEMTI4MzYwMDQ4NzhBMyA4OTc2NzExN0UyMkFGQzQ5QjU0OTA0QUY2OTE2QTVGQyAzNkYyNTZBQkMxRkUyNzQ3QkM4MDkzMTY2MTVFNUEwRCA0M0VGNTYwQjJEQ0M5NzRDOTY1Qjg0Mzk5RDZCMEQzRiA3M0UwNjlCRjJDNkYzQTRCOEREODE1MURBOUQ5MjYyQSAwREQ0MjNFRjc0Rjg5QzQ0OTY0OTJBNTUxMzJCMUQxNiAzNTg0MDFBNjM3NTcwMTQ2QUZDNTFBNzAyNEE4QkQ5RSBCQTgwNjJFRjI2MDE3QTRDQUVDQTVENjRFNjA5ODBEMyA0M0Q3N0VEQUVBRUFBRTQyOTU2OURCNjgxOUMyMUNERiA4OUQ2RDdBRDZGRTE1QjQ1QjQwRDA0OUU2OTkyRUNBMyBFRURBQzlGQTIzQ0NGMTQ0ODBCMDU0NDRGMEMwMzExMSA5OEM1RkZBRDk2MDQxMzRGODRCMDMyOUUyOUNEMUY5NyA5RjNDNEI0MDE1N0Y2QTQzQTk3M0NBQkExMTZFMDlCOSAzOUM1NUUzMTIxQjdBODRCOTExNkRGQThGMTAzNkY0RSAwRUU4MEQ5NzYyOTQ2NjQxODNCMkRDNzIxNzQxMzdGMiA1NjIyOUYyMzY1RTA0MDREOTQ2MTdGODJBRUI1REYyMCBEMTgyODNENjlFMDNCODRFQkQ0NkM5OTZFMEMxNUE0NCA0QTJGQjAxMjQwQzlENTRFQUE5RkYyMTk4Njk3NTQzMyBEN0M0QzEzQzcxOUEwRTQ1QTM4MEU4RTlDMjI3ODlGQiA1MUVGQTM1OEJCNTc2QjQ3ODAwQzY0Q0QyNUI2MjBGQyBCNjA1RTdCRjhBQjJCMzQxOEFBNzFFNkNDMTk0NUI1MyA4QTJBQUQ2MzYyQzgzRjRFODUyM0U3QzU1MEE3NzRGOCA1QjA5QzAzRjc0MUE1MDQ1QkY2QjAyNjY3Mzc2MzQzQiAxNDVCQjMyNkZERkY2RDREOUNBQjlDRTI4MjdDM0QwOBQrAxhnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dkZAICD2QWAmYPZBYEAgQPFgIeBXZhbHVlBQsxMi1BcHItMjAyMhYGZg8PFgYeBFRleHQFCzEyLUFwci0yMDIyHgVXaWR0aBsAAAAAAABZQAEAAAAeBF8hU0ICgAIWCB4HT25Gb2N1cwURX1dhdGVyTWFyayh0aGlzKTseBk9uQmx1cgURX0NoZWNrRGF0ZSh0aGlzKTseBmZvcm1hdAULZGQtTU1NLXl5eXkeDkNhbGVuZGFyRm9ybWF0BQExZAIBDw8WBh4IQ3NzQ2xhc3MFCHZzQnV0dG9uHghJbWFnZVVybAXGAS9zZWFyY2gvV2ViUmVzb3VyY2UuYXhkP2Q9b21DSXRDckZBTVRYb0dJc3ZLQVdCbG9rSWlCaF9ubmdncHkwdFRvRndzaEIwdUw1R3FxdWpwMktkbnRSbTZQRHNWMkl2NDhzUzN3bUlxTTBlRzJDTVZnUnR1eW1YemdVelA5bTV4Z0pTVkd0ZFRkMVBBcXVrQ3ZzbVFHVklMaGdEWG1TbG55NTVsODhjRWlFcWI0dFdBMiZ0PTYzNzM2NjQ1ODgxMTc1NTQ0MR8MAgJkZAICDxYKHgZGb3JtYXQFC2RkLU1NTS15eXl5HxEFCnZzQ2FsZW5kYXIeCkJlaGF2aW9ySUQFDmRwRnJvbURhdGVfRFBDHg5PbkNsaWVudEhpZGRlbgUTZHBfb25DYWxlbmRhckhpZGRlbh4NT25DbGllbnRTaG93bgUSZHBfb25DYWxlbmRhclNob3duZAIHDxYCHwkFCzEyLUFwci0yMDIyFgZmDw8WBh8KBQsxMi1BcHItMjAyMh8LGwAAAAAAAFlAAQAAAB8MAoACFggfDQURX1dhdGVyTWFyayh0aGlzKTsfDgURX0NoZWNrRGF0ZSh0aGlzKTsfDwULZGQtTU1NLXl5eXkfEAUBMWQCAQ8PFgYfEQUIdnNCdXR0b24fEgXGAS9zZWFyY2gvV2ViUmVzb3VyY2UuYXhkP2Q9b21DSXRDckZBTVRYb0dJc3ZLQVdCbG9rSWlCaF9ubmdncHkwdFRvRndzaEIwdUw1R3FxdWpwMktkbnRSbTZQRHNWMkl2NDhzUzN3bUlxTTBlRzJDTVZnUnR1eW1YemdVelA5bTV4Z0pTVkd0ZFRkMVBBcXVrQ3ZzbVFHVklMaGdEWG1TbG55NTVsODhjRWlFcWI0dFdBMiZ0PTYzNzM2NjQ1ODgxMTc1NTQ0MR8MAgJkZAICDxYKHxMFC2RkLU1NTS15eXl5HxEFCnZzQ2FsZW5kYXIfFAUMZHBUb0RhdGVfRFBDHxUFE2RwX29uQ2FsZW5kYXJIaWRkZW4fFgUSZHBfb25DYWxlbmRhclNob3duZAIPDw8WAh4HVmlzaWJsZWdkFgQCAQ8PFgIfF2dkFgYCAw8PFgIfCgUBMmRkAgkPDxYCHwoFATFkZAINDw8WAh8KBQExZGQCAw88KwARAwAPFgQeC18hRGF0YUJvdW5kZx4LXyFJdGVtQ291bnQCAmQBEBYAFgAWAAwUKwAAFgJmD2QWBgIBD2QWCmYPZBYEAgEPDxYEHwoFDEFsaXggUGFja2FyZB4PQ29tbWFuZEFyZ3VtZW50BSBEQzc2MzA1QzFCNkIxQjYwRTA1NDAwMTQ0RkZFMDUxNGRkAgMPDxYCHwoFEFZvbHVudGFyeSBVbnBhaWRkZAIBD2QWBAIBDw8WAh8KBRFCeS1sYXcvUmVndWxhdGlvbmRkAgMPDxYCHwoFKERvZyBiaXRlIHByZXZlbnRpb24gcHJvZ3JhbSBmb3IgY2hpbGRyZW5kZAICD2QWAgIBDw8WAh8KBTJDaGFwbWFuLCBSb2dlciAtIERpciwgQnktTGF3ICYgUmVndWxhdG9yeSBTZXJ2aWNlc2RkAgMPZBYEAgEPDxYCHwoFCzEyLUFwci0yMDIyZGQCAw8PFgIfCgULMTItQXByLTIwMjJkZAIED2QWAgIBDw8WAh8aBSA1NDEzMjNCMTFBOThFMjREOERGMjNFQzQ2QjU0NDdDMGRkAgIPZBYKZg9kFgQCAQ8PFgQfCgULVG9ueSBNaWxsZXIfGgUgQkMzRjg1OTFCOTM5MjNEQUUwNTQwMDE0NEZGRTA1MTRkZAIDDw8WAh8KBQhJbi1ob3VzZWRkAgEPZBYEAgEPDxYCHwoFBkJ1ZGdldGRkAgMPDxYCHwoFSVN1Ym1pc3Npb24gb2YgT1NMQSdzIHJlc3BvbnNlIHRvIHRoZSBmZWRlcmFsIGJ1ZGdldCAyMDIyIHRvIGNpdHkgY291bmNpbC5kZAICD2QWAgIBDw8WAh8KBZMHRWdsaSwgS2VpdGggLSBXYXJkL1F1YXJ0aWVyIDk8YnIvPkRlYW5zLCBEaWFuZSAtIFdhcmQvUXVhcnRpZXIgMTA8YnIvPkJyb2NraW5ndG9uLCBSaWxleSAtIFdhcmQvUXVhcnRpZXIgMTY8YnIvPlRoZXJlc2EgS2F2YW5hdWdoIC0gV2FyZC9RdWFydGllciA3PGJyLz5NY0tlbm5leSwgQ2F0aGVyaW5lIC0gV2FyZC9RdWFydGllciAxNDxici8%2BV2F0c29uLCBKaW0gLSBNYXlvci9NYWlyZTxici8%2BTW9mZmF0dCwgU2NvdHQgLSBXYXJkL1F1YXJ0aWVyIDIxPGJyLz5OdXNzYmF1bSwgVG9iaSAtIFdhcmQvUXVhcnRpZXIgMTM8YnIvPkVsLUNoYW50aXJ5LCBFbGkgLSBXYXJkL1F1YXJ0aWVyIDU8YnIvPlRpZXJuZXksIFRpbSAtIFdhcmQvUXVhcnRpZXIgMTE8YnIvPkRhcm91emUsIEdlb3JnZSAtIFdhcmQvUXVhcnRpZXIgMjA8YnIvPkNoaWFyZWxsaSwgUmljayAtIFdhcmQvUXVhcnRpZXIgODxici8%2BSGFyZGVyLCBKYW4gLSBXYXJkL1F1YXJ0aWVyIDM8YnIvPlNoYXduIE1lbmFyZCAtIFdhcmQvUXVhcnRpZXIgMTc8YnIvPkZsZXVyeSwgTWF0aGlldSAtIFdhcmQvUXVhcnRpZXIgMTI8YnIvPkplbm5hIFN1ZGRzIC0gV2FyZC9RdWFydGllciA0PGJyLz5DYXRoZXJpbmUgS2l0dHMgLSBXYXJkL1F1YXJ0aWVyIDE5PGJyLz5XYXRzb24sIEppbSAtIE1heW9yL01haXJlPGJyLz5MZWlwZXIsIEplZmYgLSBXYXJkL1F1YXJ0aWVyMTU8YnIvPkh1YmxleSwgQWxsYW4gLSBXYXJkL1F1YXJ0aWVyIDIzPGJyLz5HbGVuIEdvd2VyIC0gV2FyZC9RdWFydGllciA2PGJyLz5MYXVyYSBEdWRhcyAtIFdhcmQvUXVhcnRpZXIgMjxici8%2BTWF0dGhldyBMdWxvZmYgLSBXYXJkL1F1YXJ0aWVyIDE8YnIvPkNhcm9sIEFubmUgTWVlaGFuIC0gV2FyZC9RdWFydGllciAyMjxici8%2BQ2xvdXRpZXIsIEplYW4gLSBXYXJkL1F1YXJ0aWVyIDE4ZGQCAw9kFgQCAQ8PFgIfCgULMTItQXByLTIwMjJkZAIDDw8WAh8KBQsxMi1BcHItMjAyMmRkAgQPZBYCAgEPDxYCHxoFIDkyQTI1MTg0REJBQTI2NDE4OUVEMkJDODlFQTA1MTdDZGQCAw8PFgIfF2hkZAIRD2QWFAIBDw8WAh8KZWRkAgMPDxYCHwplZGQCBQ8PFgIfCmVkZAIHDw8WAh8KZWRkAgkPDxYCHwplZGQCDQ9kFgQCAQ8PFgIfCmVkZAIDDw8WAh8KZWRkAhEPDxYCHwplZGQCFQ8PFgIfCmVkZAIbDw8WAh8KZWRkAiEPDxYCHwplZGQCEw9kFgICAQ88KwARAwAPFgQfGGcfGWZkARAWABYAFgAMFCsAAGQCFQ9kFgoCAQ9kFgoCAQ8PFgIfCmVkZAIDDw8WAh8KZWRkAgUPDxYCHwplZGQCBw8PFgIfCmVkZAILD2QWAgIBDw8WAh8KZWRkAgUPDxYCHwplZGQCCQ8PFgIfCmVkZAINDw8WAh8KZWRkAhEPDxYCHwplZGQCFw9kFgICAQ88KwARAwAPFgQfGGcfGWZkARAWABYAFgAMFCsAAGQYBAUeX19Db250cm9sc1JlcXVpcmVQb3N0QmFja0tleV9fFgQFIWN0bDAwJE1haW5Db250ZW50JGxiU3ViamVjdE1hdHRlcgUYY3RsMDAkTWFpbkNvbnRlbnQkbGJXYXJkBSBjdGwwMCRNYWluQ29udGVudCRkcEZyb21EYXRlX2ltZwUeY3RsMDAkTWFpbkNvbnRlbnQkZHBUb0RhdGVfaW1nBRxjdGwwMCRNYWluQ29udGVudCRndkFjdGl2aXR5DzwrAAwBCGZkBR9jdGwwMCRNYWluQ29udGVudCRndlVuZGVydGFraW5nDzwrAAwBCGZkBSFjdGwwMCRNYWluQ29udGVudCRndlNlYXJjaFJlc3VsdHMPPCsADAICAgEIAgFkYbn4oN99C6n2n8npcH7%2BJi9J4gWBPY3vYNzdlv5knX4%3D&__VIEWSTATEGENERATOR=ECBD0EBB&__SCROLLPOSITIONX=0&__SCROLLPOSITIONY=0&__EVENTVALIDATION=%2FwEdAFdqrP%2FW719CtO8dKH6lNX00McIY5WrtjAE9fhaEQyjPSdkfXHM4sTUclO8PBLVWZd6X2%2F3yDImLD2n7LoK1YRS63GTegZhh4nvpLlzccdSP1QMWPWG42XSTYVqztKDkB02vWJqCzuiVLq1%2FEnzkcsTnu0XAQG5Wykw6Ku1wJbyKSKU8yLWWhjj8MUKcV5SuZJsuH90Hj91hcvnl%2FH6ihwUvLtQmvduv%2FHCYsM5lnE1ZNDKicbv%2BLwo23laTWdBmpkd9ENaecBA5Y1o9163A520pPWGGfFQx3tWOdYAMPn4EyvV28q5Kt8NoAJcOQuLFUg3quFxwZawhV0PBwJwcKbKXj3zlo3v%2FRDmmsYlB2cLBMpDR27Lhu%2B2B3rqJigLZjiH35uTUYqYtitpOdw3PYXYRH7L4B0n8Vn3CVrilww7nd%2BMLWqCTllmZoi%2FxfUgFIyojh%2BHrIYHld%2BZrXf6OHJpS98PxEcULIZ6oGSikX379wyQkiDHzMxfr8nOjBbuYglZyQ9OEOLLRj64SqyWzH7ISNPhXQYyOTdmGYioCnj3H1BvnwQXSntZXcX31FprH%2FdZMUsd4Et1rnrjcu4cPCCffUVUe2%2BoIDsQ7DAtRyw4AhUO2MVdTCX4YcOeFm93HRFKAUwHVSIq%2F9YWbcJ1o6HPrICTQgmQHZdPVh9MbYjVc2TqRDoBORaAu%2FcKoDoTt94bsG1qRVgPeYboFMDup0uc4NUhyrXu0PPRRuKQTC%2B191zU37GglNa1QPeGrJmCt2hBCwhJiM73xTxPep6vwfK39%2B0ztiKyZ2R8dAZEtm377v71zWi10QOSwvD7JmioNPU7EJqUyfusgrI6yrR8kGujuGvySFQSVmDzHjHBpRIYFZMIeXtxjUS%2BNxTzPETpymR3IpaUeQ6ghL9bkvitfGdFIrPb7vGALeInjq5L87WAR41iO%2BW7gb4FtHjKGMs2c%2F4tu2FfzMnG0ozgTW1inFrQMPFOno9L6l9K7K9520m5hpsPkHA73lUgLf9QuHVm9kaSbgbFg45IBXGTVOBfzLvkr%2B6MkgitaUH3cXSfZKh5i%2BNv7jhvhbcFJm7wk%2F2BHzfmG41FgNcH2B8c6xZVKRAnaRNVEMtov6lySs4AlW8SQqXURJ2JIC9xTKhiqGnDb2UzNN%2Fryayb1Gfwd6C0kzY5pQC1NibyK5kpxfhW3iFmHzDXea8odHP3iv%2BvVT8FqmAcA7QlD0nw8XsEFapQWoR3xgDqkCbItavDB44zfY4vKR8PN3hNJzblNX1fsR4BFnLuMJUHkTmpXKBm18h9pxRetzs3aIzVrpf%2FA5fWPTBTuE0eXaVHA%2FAh2rVTWmqZqJspov5434%2BwVQXgay05T6VbPTe9KTqGUq0KBzXsy0l7B7L%2BNAdgRRWNxFbwNjOWcL9iqNDnZVzpLURxzHrq4KZfZgJHXhMZ6yGgb5FKWxiNex6PUqlHad6gsZ64GC4HCcWXbpxVWCk3CxIqjNfIl0pry4LEdQvToQQXQncij0R6lgvpZ8unkzy9booJkcvMLR9xmc6RtkvqYM3hIJhFGKFFkF1C9chB5AS6cdTAy1Lf%2FLfYL%2FcyiUGsqte8rwQwXhdbavD%2BPDPXYD4nReY46rqgtgzXoR1hTmy%2FXi68C8UALY5%2BI%2FHPZ86NEZcbXwR9jxA6UPEhGhshETMudCSHqB6l2sFU%2BCDAjuGTCsbatzyQyjVYoPoNmRVIioaqqUeXedX8bd2mWDfrxIzCEBVFrUPY8CCL%2Fcu4e2kdOzG%2BqSwXaL54w7w11kxZoLYfLqhnYmRqyH2062mzvii4R1B7JmXQ3WKnE%2FfQdMxyE%2FIgmBK399%2Ft1wwOjeY1U3%2B3100ummhF1wBlCGLsnx53AkqhBjNlDKTU01Cs%2FhloCDtvMwQnZqA%3D%3D&ctl00%24MainContent%24txtKeyword=&ctl00%24MainContent%24TextBoxWatermarkExtender1_ClientState=&ctl00%24MainContent%24txtLobbyist=&ctl00%24MainContent%24txtPersonsLobbied=&ctl00%24MainContent%24WM2_ClientState=&ctl00%24MainContent%24dpFromDate_txtbox=#{formatted_date}&ctl00%24MainContent%24dpToDate_txtbox=#{formatted_date}&ctl00%24MainContent%24btnSearch=Search&ctl00%24MainContent%24cbPageSize=10&hiddenInputToUpdateATBuffer_CommonToolkitScripts=1"
    post(body).body
  end

  def lobbying_entry(params)
    post(URI.encode_www_form(params)).body
  end

  def post(body)
    uri = URI.parse(SEARCH_URL)
    request = Net::HTTP::Post.new(uri)
    request.content_type = "application/x-www-form-urlencoded"
    request.body = body
    req_options = {
      use_ssl: uri.scheme == "https",
    }
    Net::HTTP.start(uri.hostname, uri.port, req_options) do |http|
      http.request(request)
    end
  end

  def view_state(doc)
    doc.xpath('//input[@name="__VIEWSTATE"]/@value').first.value
  end

  def event_validation(doc)
    doc.xpath('//input[@name="__EVENTVALIDATION"]/@value').first.value
  end
end

