class AddReferenceGuidToMeetings < ActiveRecord::Migration[7.0]
  def change
    add_column :meetings, :reference_guid, :string
  end
end
