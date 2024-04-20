function sendDataToServer(userIds, path) {
  console.log(path)
  $.ajax({
    url: path,
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({userIds: userIds}),
    beforeSend: function () {
      $('.loader').show();
    },
    success: function (data) {
      $('.loader').hide();
      updateTableWithData(data.users);
    },
    error: function () {
      $('.loader').hide();
      console.log('Ajax request failed.');
    },
    complete: function (xhr, status) {
      if (xhr.status === 403) {
        window.location.reload();
      }
      console.log('Ajax request completed with status: ' + status);
    }
  });
}
