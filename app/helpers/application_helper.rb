module ApplicationHelper
  def new_campaign_return_path(candidate:)
    super(candidate_id: candidate.id)
  end
end
