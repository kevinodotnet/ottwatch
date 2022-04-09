class AddStateToDevAppDocuments < ActiveRecord::Migration[7.0]
  def change
    add_column :dev_app_documents, :state, :string
  end
end
