document.addEventListener('DOMContentLoaded', function () {
  // Click handler for unblock button
  $('#unblock_button').on('click', function () {
    let checkboxes = $('input[type="checkbox"]:checked');
    let selectedUserIds = [];

    checkboxes.each(function (index) {
      selectedUserIds.push($(checkboxes[index]).data('userId'));
      if (selectedUserIds[0] == null) {
        selectedUserIds = selectedUserIds.slice(1)
      }
    });

    sendDataToServer(selectedUserIds, '/unblock_users');
  });

  // Click handler for block button
  $('#block_button').on('click', function () {
    let checkboxes = $('input[type="checkbox"]:checked');
    let selectedUserIds = [];

    checkboxes.each(function (index) {
      selectedUserIds.push($(checkboxes[index]).data('userId'));
      if (selectedUserIds[0] == null) {
        selectedUserIds = selectedUserIds.slice(1)
      }
    });

    sendDataToServer(selectedUserIds, '/block_users');
  });

  // Click handler for delete button
  $('#delete_button').on('click', function () {
    let checkboxes = $('input[type="checkbox"]:checked');
    let selectedUserIds = [];

    checkboxes.each(function (index) {
      selectedUserIds.push($(checkboxes[index]).data('userId'));
      if (selectedUserIds[0] == null) {
        selectedUserIds = selectedUserIds.slice(1)
      }
    });

    sendDataToServer(selectedUserIds, '/delete_users');
  });

  // Click handler for check all checkboxes
  $('#selectAllCheckbox').on('click', function () {
    let state = $('#selectAllCheckbox').prop('checked');
    changeCheckboxesState(state);
  });
});

function changeCheckboxesState(state) {
  $('#selectAllCheckbox').prop('checked', state);

  let checkboxes = $('input[type="checkbox"][data-user-id]:not(#selectAllCheckbox)');
  checkboxes.each(function (index) {
    $(checkboxes[index]).prop('checked', state);
  });
}

function resetCheckboxesState() {
  $('#selectAllCheckbox').prop('checked', false);

  $('input[type="checkbox"][data-user-id]:not(#selectAllCheckbox)').prop('checked', false);
}
