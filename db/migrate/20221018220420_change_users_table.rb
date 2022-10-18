class ChangeUsersTable < ActiveRecord::Migration[7.0]
  def change
    change_column :users, :email, :string, null: true
    remove_index :users, [:email] # , name => "index_completions_on_survey_id_and_user_id"

  end
end
