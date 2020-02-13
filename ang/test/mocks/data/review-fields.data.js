(function () {
  var module = angular.module('civiawards.data');

  module.constant('ReviewFieldsMockData', [
    {
      id: '19',
      custom_group_id: '8',
      name: 'Goals_Scored',
      label: 'Goals Scored',
      data_type: 'String',
      html_type: 'Text',
      is_search_range: '0',
      weight: '1',
      is_active: '1',
      text_length: '255',
      note_columns: '60',
      note_rows: '4',
      column_name: 'goals_scored_19',
      in_selector: '0'
    }, {
      id: '20',
      custom_group_id: '8',
      name: 'Number_of_Dribbles_per_game',
      label: 'Number of Dribbles per game',
      data_type: 'String',
      html_type: 'Text',
      is_search_range: '0',
      weight: '2',
      is_active: '1',
      text_length: '255',
      note_columns: '60',
      note_rows: '4',
      column_name: 'number_of_dribbles_per_game_20',
      in_selector: '0'
    }, {
      id: '45',
      custom_group_id: '9',
      name: 'Age',
      label: 'Age',
      data_type: 'Float',
      html_type: 'Text',
      is_search_range: '0',
      weight: '3',
      is_active: '1',
      text_length: '255',
      note_columns: '60',
      note_rows: '4',
      column_name: 'age_21',
      in_selector: '0'
    }
  ]);
}());
