class MemoController < ApplicationController
  def index
    @memos = Memo.recent.includes(:announcements)
    @memos = @memos.by_department(params[:department]) if params[:department].present?
    @memos = @memos.page(params[:page]).per(20)
    @departments = Memo.distinct.pluck(:department).compact.sort
  end

  def show
    @memo = Memo.find(params[:id])
  rescue ActiveRecord::RecordNotFound
    redirect_to memo_index_path, alert: "Memo not found"
  end
end
