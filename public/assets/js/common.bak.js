let switcheryDefaults = {
    color: '#1AB394',
    secondaryColor: '#ED5565',
    className: 'switchery',
    size: 'small'
};

let toastrDefaults = {
    closeButton: true,
    debug: false,
    newestOnTop: false,
    progressBar: true,
    positionClass: 'toast-top-right',
    preventDuplicates: false,
    onclick: null,
    showDuration: 300,
    hideDuration: 1000,
    timeOut: 5000,
    extendedTimeOut: 1000,
    showEasing: 'swing',
    hideEasing: 'linear',
    showMethod: 'fadeIn',
    hideMethod: 'fadeOut'
};

function capitalizeFirstLetter(word) {
    return word.charAt(0).toUpperCase() + word.slice(1);
}

function pluralize(word) {

    if ('category' === word) {
        return 'categories';
    }

    if ('Category' === word) {
        return 'Categories';
    }

    return word + 's';
}

function showToast(type, title, text) {
    switch (type) {
        case 'success':
            toastr.success(text, title, toastrDefaults);
            break;
        case 'error':
            toastr.error(text, title, toastrDefaults);
            break;
        default:
            toastr.info(text, title, toastrDefaults);
    }
}

function createEntity(entity, options) {
    let reloadDataTable = (options.reloadDataTable === undefined) ? false : options.reloadDataTable;
    let showNotification = (options.showNotification === undefined) ? false : options.showNotification;

    $.ajax({
        url: options.url,
        method: 'POST',
        dataType: 'json',
        data: {
            entity: entity,
            fields: options.fields
        },
        success: function (response) {
            if (response.success) {
                if (reloadDataTable) {
                    $('#' + pluralize(entity.toLowerCase()) + '-datatable').dataTable().api().ajax.reload();
                }

                if (showNotification) {
                    showToast('success', 'Success', entity + ' saved');
                }
            }
        },
        error: function (xhr, status, error) {
            showToast('error', 'Error', status + ': ' + error);
        }
    });
}

function updateEntity(entity, options) {
    let reloadDataTable = (options.reloadDataTable === undefined) ? false : options.reloadDataTable;
    let showNotification = (options.showNotification === undefined) ? false : options.showNotification;

    $.ajax({
        url: options.url,
        method: 'PATCH',
        dataType: 'json',
        data: {
            entity: entity,
            id: options.id,
            fields: options.fields
        },
        success: function (response) {
            if (response.success) {
                if (reloadDataTable) {
                    $('#' + pluralize(entity.toLowerCase()) + '-datatable').dataTable().api().ajax.reload();
                }

                if (showNotification) {
                    showToast('success', 'Success', entity + ' updated');
                }
            }
        },
        error: function (xhr, status, error) {
            showToast('error', 'Error', status + ':' + error);
        }
    });
}

function deleteEntity(entity, options)
{
    let reloadDataTable = (options.reloadDataTable === undefined) ? false : options.reloadDataTable;
    let showNotification = (options.showNotification === undefined) ? false : options.showNotification;

    $.ajax({
        url: options.url,
        method: 'DELETE',
        dataType: 'json',
        data: {
            entity: entity,
            id: options.id
        },
        success: function (response) {
            if (response.success) {
                if (reloadDataTable) {
                    $('#' + pluralize(entity.toLowerCase()) + '-datatable').dataTable().api().ajax.reload();
                }

                if (showNotification) {
                    showToast('success', 'Success', entity + ' deleted');
                }
            } else {
                Swal.fire({
                    type: 'error',
                    title: 'Ooops..',
                    text: response.message
                });
            }
        },
        error: function (xhr, status, error) {
            showToast('error', 'Error', status + ': ' + error);
        }
    });
}
