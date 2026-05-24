$(function () {
    $('.modalButton').click(function (event) {
        event.preventDefault()
        $('#update').modal('show').find('#modalContentUpdate').load($(this).attr('data-value'));
    })
    $('#createModal').click(function (event) {
        event.preventDefault()
        $('#create').modal('show').find('#modalContentCreate').load($(this).attr('data-value'));
    })
    $('#createModalQiwi').click(function (event) {
        event.preventDefault()
        $('#create').modal('show').find('#modalContentCreate').load($(this).attr('data-value'));
    })
    $('#createServiceModal').click(function (event) {
        event.preventDefault()
        $('#create').modal('show').find('#modalContentCreate').load($(this).attr('data-value'));
    })
    $('#changeOperationsModal').click(function (event) {
        event.preventDefault()
        $('#changeOperations').modal('show').find('#modalChangeOperations').load($(this).attr('data-value'));
    })
})