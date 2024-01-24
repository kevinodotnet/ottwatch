class AnnouncementController < ApplicationController
  def index
    relation = Announcement.all
    relation = relation.where(reference_type: params[:reference_type]) if params[:reference_type]
    relation = relation.where('id < ?', params[:before_id]) if params[:before_id]
    relation = relation.limit(params[:limit] || 50).includes(:reference)
    @announcements = relation.order(id: :desc).to_a
  end
end
