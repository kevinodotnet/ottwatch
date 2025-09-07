class MemoController < ApplicationController
  def index
    @memos = Memo.recent # .includes(:announcements)
    @memos = @memos.by_department(params[:department]) if params[:department].present?
    @memos = @memos.order(issued_date: :desc)
    @departments = Memo.distinct.pluck(:department).compact.sort
  end

  def show
    @memo = Memo.find(params[:id])
    
    if @memo.content.present?
      doc = Nokogiri::HTML::DocumentFragment.parse(@memo.content)
      doc.search('*').each do |element|
        element.remove_attribute('class')
        element.remove_attribute('style')
      end
      
      loop do
        changes_made = false
        
        # Remove empty elements
        doc.search('*').each do |element|
          if element.content.strip.empty?
            element.remove
            changes_made = true
          end
        end
        
        # Unwrap single-child elements
        doc.search('*').each do |element|
          if element.children.length == 1 && element.children.first.is_a?(Nokogiri::XML::Element)
            child = element.children.first
            element.replace(child)
            changes_made = true
          end
        end
        
        break unless changes_made
      end
      
      @memo.content = doc.to_html
    end
  rescue ActiveRecord::RecordNotFound
    redirect_to memo_index_path, alert: "Memo not found"
  end
end
