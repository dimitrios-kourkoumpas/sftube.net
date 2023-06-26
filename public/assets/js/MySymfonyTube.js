let MySymfonyTube = {
    switcheryDefaults: {
        color: '#1AB394',
        secondaryColor: '#ED5565',
        className: 'switchery',
        size: 'small',
        disabled: false
    },
    toastrDefaults: {
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
    },
    constants: {
        toastTypes: {
            SUCCESS: 'success',
            ERROR: 'error',
            INFO: 'info',
        },
        HTTP_OK: 200,
        HTTP_CREATED: 201,
        HTTP_NO_CONTENT: 204,
        HTTP_UNPROCESSABLE_ENTITY: 422
    },
    capitalizeFirstLetter: function (word) {
        return word.charAt(0).toUpperCase() + word.slice(1);
    },
    pluralize: function (word) {
        if ('category' === word) {
            return 'categories';
        }

        if ('Category' === word) {
            return 'Categories';
        }

        return word + 's';
    },
    showToast: function (type, title, text) {
        toastrDefaults = this.toastrDefaults;

        switch (type) {
            case this.constants.toastTypes.SUCCESS:
                toastr.success(text, title, toastrDefaults);
                break;
            case this.constants.toastTypes.ERROR:
                toastr.error(text, title, toastrDefaults);
                break;
            default:
                toastr.info(text, title, toastrDefaults);
        }
    },
    populateModalForm: function (options) {
        $.ajax({
            url: options.url,
            method: 'GET',
            success: function (data, status, xhr) {
                if (xhr.status === MySymfonyTube.constants.HTTP_OK) {
                    data = JSON.parse(data);

                    $.each(data, function (key, value) {
                        let input = options.meta.modal.find('[name=' + key + ']');

                        input.val(value);
                    });

                    $(options.meta.modal).modal('show');
                } else {
                    MySymfonyTube.showToast(MySymfonyTube.constants.toastTypes.ERROR, 'Error', 'An error occurred');
                }
            },
            error: function (xhr, status, error) {
                MySymfonyTube.showToast(MySymfonyTube.constants.toastTypes.ERROR, 'Error', status + ': ' + error);
            }
        });
    },
    getFormData: function (form) {
        let formArray = form.serializeArray();

        let data = {};

        for (let i = 0; i < formArray.length; i++) {
            data[formArray[i]['name']] = formArray[i]['value'];
        }

        return data;
    },
    create: function (entity, options) {
        let reloadDataTable = (options.reloadDataTable === undefined) ? false : options.reloadDataTable;
        let showNotification = (options.showNotification === undefined) ? false : options.showNotification;

        $.ajax({
            url: options.url,
            method: 'POST',
            dataType: 'json',
            data: JSON.stringify(options.fields),
            success: function (response, status, xhr) {
                if (xhr.status === MySymfonyTube.constants.HTTP_CREATED) {
                    if (reloadDataTable) {
                        $('#' + options.meta.slug + '-datatable').DataTable().ajax.reload();
                    }

                    if (showNotification) {
                        MySymfonyTube.showToast(MySymfonyTube.constants.toastTypes.SUCCESS, 'Success', entity + ' saved');
                    }

                    MySymfonyTube.hideAndClearModal(options.meta.modal);
                } else {
                    MySymfonyTube.showToast(MySymfonyTube.constants.toastTypes.ERROR, 'Error', entity + ' not saved');
                }
            },
            error: function (xhr, status, error) {
                if (xhr.status === MySymfonyTube.constants.HTTP_UNPROCESSABLE_ENTITY) {
                    const response = xhr.responseJSON;

                    MySymfonyTube.showToast(MySymfonyTube.constants.toastTypes.ERROR, response.title, error);

                    MySymfonyTube.highlightViolations(options.meta.modal, response.violations);
                } else {
                    MySymfonyTube.showToast(MySymfonyTube.constants.toastTypes.ERROR, 'Error', error);
                }
            }
        });
    },
    update: function (entity, options) {
        let reloadDataTable = (options.reloadDataTable === undefined) ? false : options.reloadDataTable;
        let showNotification = (options.showNotification === undefined) ? false : options.showNotification;

        $.ajax({
            url: options.url,
            method: 'PATCH',
            dataType: 'json',
            data: JSON.stringify(options.fields),
            success: function (response, status, xhr) {
                if (xhr.status === MySymfonyTube.constants.HTTP_OK) {
                    if (reloadDataTable) {
                        $('#' + options.meta.slug + '-datatable').DataTable().ajax.reload();
                    }

                    if (showNotification) {
                        MySymfonyTube.showToast(MySymfonyTube.constants.toastTypes.SUCCESS, 'Success', entity + ' updated');
                    }

                    if (options.meta !== undefined && options.meta.modal !== undefined) {
                        MySymfonyTube.hideAndClearModal(options.meta.modal);
                    }
                }
            },
            error: function (xhr, status, error) {
                if (xhr.status === MySymfonyTube.constants.HTTP_UNPROCESSABLE_ENTITY) {
                    const response = xhr.responseJSON;

                    MySymfonyTube.showToast(MySymfonyTube.constants.toastTypes.ERROR, response.title, error);

                    MySymfonyTube.highlightViolations(options.meta.modal, response.violations);
                } else {
                    MySymfonyTube.showToast(MySymfonyTube.constants.toastTypes.ERROR, 'Error', error);
                }
            }
        });
    },
    delete: function (entity, options) {
        let reloadDataTable = (options.reloadDataTable === undefined) ? false : options.reloadDataTable;
        let showNotification = (options.showNotification === undefined) ? false : options.showNotification;

        $.ajax({
            url: options.url,
            method: 'DELETE',
            success: function (response, status, xhr) {
                if (xhr.status === MySymfonyTube.constants.HTTP_NO_CONTENT) {
                    if (reloadDataTable) {
                        $('#' + options.meta.slug + '-datatable').DataTable().ajax.reload();
                    }

                    if (showNotification) {
                        MySymfonyTube.showToast(MySymfonyTube.constants.toastTypes.SUCCESS, 'Success', entity + ' deleted');
                    }
                } else {
                    MySymfonyTube.showToast(MySymfonyTube.constants.toastTypes.ERROR, 'Error', entity + ' not deleted');
                }
            },
            error: function (xhr, status, error) {
                MySymfonyTube.showToast('error', 'Error', status + ': ' + error);
            }
        });
    },
    hideAndClearModal: function (modal) {
        modal.modal('hide');

        modal.on('hidden.bs.modal', function (e) {
            $(this)
                .find("input,textarea,select")
                .val('')
                .end()
                .find("input[type=checkbox], input[type=radio]")
                .prop("checked", "")
                .end();
        });
    },
    highlightViolations: function (modal, violations) {
        $(violations).each(function (i, v) {
            const parentDiv = $(modal).find('input[name=' + v.propertyPath + ']').parent();

            $(parentDiv).append($('<span>', {
                class: 'help-block',
                text: v.message
            }));

            $(parentDiv).addClass('has-error');
        });
    },
    clearModal: function (modal) {
        $(modal).find('div.form-group').removeClass('has-error');
        $(modal).find('span.help-block').remove();

        $(modal)
            .find("input,textarea,select")
            .val('')
            .end()
            .find("input[type=checkbox], input[type=radio]")
            .prop("checked", "")
            .end();
    }
};
