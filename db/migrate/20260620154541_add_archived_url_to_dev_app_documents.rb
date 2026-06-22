class AddArchivedUrlToDevAppDocuments < ActiveRecord::Migration[8.0]
  def change
    add_column :dev_app_documents, :archived_url, :string
  end
end
