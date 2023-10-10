SFTube = {
    constants: {
        HTTP_UNPROCESSABLE_ENTITY: 422
    },
    toastr: {
        defaults: {
            'closeButton': true,
            'progressBar': true
        },
        success: 'Success',
        error: 'Error'
    },
    switchery: {
        defaults: {
            color: '#1AB394',
            secondaryColor: '#ED5565',
            className: 'switchery',
            size: 'small',
            disabled: false
        }
    },
    getFormData: function (form) {
        let formArray = form.serializeArray();

        let data = {};

        for (let i = 0; i < formArray.length; i++) {
            data[formArray[i]['name']] = formArray[i]['value'];
        }

        return data;
    },
    create: function (entitySlug, options) {
        $.ajax({
            url: options.url,
            method: 'POST',
            dataType: 'json',
            data: JSON.stringify(options.fields),
            success: function (data, status, xhr) {
                if (options.reloadDataTable) {
                    $('#' + options.meta.slug + '-datatable').DataTable().ajax.reload();
                }

                if (options.showNotification) {
                    toastr.success(options.meta.entitySingular + ' saved', SFTube.toastr.success);
                }

                SFTube.hideModal(options.meta.modal);
            },
            error: function (xhr, status, error) {
                toastr.error(error, SFTube.toastr.error);

                if (xhr.status === SFTube.constants.HTTP_UNPROCESSABLE_ENTITY) {
                    SFTube.highlightViolations(options.meta.modal, xhr.responseJSON.violations)
                }
            },
        });
    },
    update: function (options) {
        $.ajax({
            url: options.url,
            method: 'PATCH',
            data: JSON.stringify(options.fields),
            dataType: 'json',
            success: function (data, status, xhr) {
                if (options.reloadDataTable) {
                    $('#' + options.meta.slug + '-datatable').DataTable().ajax.reload();
                }

                if (options.showNotification) {
                    toastr.success(options.meta.entitySingular + ' saved', SFTube.toastr.success);
                }

                if (options.meta.modal !== undefined) {
                    SFTube.hideModal(options.meta.modal);
                }
            },
            error: function (xhr, status, error) {
                toastr.error(error, SFTube.toastr.error);

                if (xhr.status === SFTube.constants.HTTP_UNPROCESSABLE_ENTITY) {
                    SFTube.highlightViolations(options.meta.modal, xhr.responseJSON.violations)
                }
            }
        });
    },
    delete: function (options) {
        $.ajax({
            url: options.url,
            method: 'DELETE',
            dataType: 'json',
            success: function (data, status, xhr) {
                if (options.reloadDataTable) {
                    $('#' + options.meta.slug + '-datatable').DataTable().ajax.reload();
                }

                if (options.showNotification) {
                    toastr.success(options.meta.entitySingular + ' deleted', SFTube.toastr.success);
                }
            },
            error: function (xhr, status, error) {
                toastr.error(error, SFTube.toastr.error);
            }
        });
    },
    populateModalForm: function (options) {
        $.ajax({
            url: options.url,
            method: 'GET',
            dataType: 'json',
            beforeSend: function (xhr, settings) {
                SFTube.clearViolations(options.meta.modal);
            },
            success: function (data, status, xhr) {
                $.each(data, function (key, value) {
                    let input = options.meta.modal.find('[name=' + key + ']');

                    input.val(value);
                });

                $(options.meta.modal).modal('show');
            },
            error: function (xhr, status, error) {
                toastr.error(error, SFTube.toastr.error)
            }
        });
    },
    hideModal: function (modal) {
        modal.modal('hide');
    },
    clearModalFields: function (modal) {
        $(modal).find('div.form-group').removeClass('has-error');
        $(modal).find('span.help-block').remove();

        $(modal)
            .find('input,textarea,select')
            .val('')
            .end()
            .find('input[type=checkbox],input[type=radio]')
            .prop('checked', '')
            .end();
    },
    clearViolations: function (modal) {
        const fields = $(modal).find('form').find('input,textarea');

        fields.each(function (i, field) {
            const parent = $(field).parent();

            parent.removeClass('has-error').find('span.help-block').remove();
        });
    },
    highlightViolations: function (modal, violations) {
        $(violations).each(function (i, v) {
            const parentDiv = $(modal).find('input[name=' + v.propertyPath + '],textarea[name=' + v.propertyPath + ']').parent();

            $(parentDiv).append($('<span>', {
                class: 'help-block',
                text: v.message
            }));

            $(parentDiv).addClass('has-error');
        });
    }
};
