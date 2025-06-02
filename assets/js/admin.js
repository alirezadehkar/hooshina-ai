jQuery(document).ready(function($){
    const HaiAdmin = {
        init: function() {
            this.bindEvents();
            this.loadInitialTerms();
            this.initAuthorSelect();
            this.initAjaxSubmitHandler();
            this.initRemindConnectionNotice();
        },

        bindEvents: function() {
            $('#hai-default-taxonomy').on('change', this.handleTaxonomyChange.bind(this));
        },

        initAuthorSelect: function() {
            $('#hai-default-author').select2({
                ajax: {
                    url: hai_data.ajax_url,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            action: 'hai_search_users',
                            search: params.term,
                            page: params.page || 1,
                            nonce: hai_data.nonce
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        
                        return {
                            results: data.data.items || [],
                            pagination: {
                                more: data.data.more || false
                            }
                        };
                    },
                    cache: true,
                    beforeSend: () => {
                        HaiAdmin.setButtonState(true);
                    },
                    complete: () => {
                        HaiAdmin.setButtonState(false);
                    }
                },
                placeholder: hai_data.texts.select,
                minimumInputLength: 2,
                templateResult: this.formatAuthor,
                templateSelection: this.formatAuthorSelection,
                language: {
                    errorLoading: function() {
                        return hai_data.texts.select2.errorLoading;
                    },
                    inputTooLong: function(args) {
                        return hai_data.texts.select2.inputTooLong.replace('%d', args.input.length - args.maximum);
                    },
                    inputTooShort: function(args) {
                        return hai_data.texts.select2.inputTooShort.replace('%d', args.minimum - args.input.length);
                    },
                    loadingMore: function() {
                        return hai_data.texts.select2.loadingMore;
                    },
                    maximumSelected: function(args) {
                        return hai_data.texts.select2.maximumSelected.replace('%d', args.maximum);
                    },
                    noResults: function() {
                        return hai_data.texts.select2.noResults;
                    },
                    searching: function() {
                        return hai_data.texts.select2.searching;
                    }
                }
            });

            const selectedAuthor = $('#hai-default-author').data('selected');
            if (selectedAuthor) {
                const option = new Option(selectedAuthor.text, selectedAuthor.id, true, true);
                $('#hai-default-author').append(option).trigger('change');
            }
        },

        formatAuthor: function(author) {
            if (author.loading) {
                return author.text;
            }

            if (!author.id) {
                return author.text;
            }

            return $(`
                <div class="hai-author-option">
                    <span class="hai-author-name">${author.text}</span>
                    <span class="hai-author-role">${author.role}</span>
                </div>
            `);
        },

        formatAuthorSelection: function(author) {
            return author.text || author.id;
        },

        loadInitialTerms: function() {
            const initialTaxonomy = $('#hai-default-taxonomy').val();
            if (initialTaxonomy) {
                this.loadTerms(initialTaxonomy);
            }
        },

        handleTaxonomyChange: function(e) {
            const taxonomy = $(e.target).val();
            this.loadTerms(taxonomy);
        },

        toggleCategoryWrapper: function(show) {
            $('#hai-category-wrapper')[show ? 'show' : 'hide']();
        },

        setCategoryLoading: function(isLoading) {
            const $categorySelect = $('#hai-default-category');
            if (isLoading) {
                $categorySelect.prop('disabled', true);
                $categorySelect.addClass('loading');
            } else {
                $categorySelect.prop('disabled', false);
                $categorySelect.removeClass('loading');
            }
        },

        setButtonState: function(isDisabled) {
            $('.hai-submit').prop('disabled', isDisabled);
        },

        loadTerms: function(taxonomy) {
            if (!taxonomy) {
                this.toggleCategoryWrapper(false);
                return;
            }

            this.setCategoryLoading(true);

            $.ajax({
                url: hai_data.ajax_url,
                type: 'POST',
                data: {
                    action: 'hai_get_terms_by_taxonomy',
                    taxonomy: taxonomy,
                    nonce: hai_data.nonce
                },
                beforeSend: () => {
                    this.setButtonState(true);
                },
                success: (response) => {
                    if (response.success) {
                        this.updateTermsList(response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error loading terms:', error);
                    this.toggleCategoryWrapper(false);
                },
                complete: () => {
                    this.setCategoryLoading(false);
                    this.setButtonState(false);
                }
            });
        },

        updateTermsList: function(terms) {
            const select = $('#hai-default-category');
            select.empty().append(`<option value="">${hai_data.texts.select}</option>`);

            if(terms){
                $.each(terms, (id, name) => {
                    select.append($('<option></option>')
                        .attr('value', id)
                        .text(name)
                        .prop('selected', id == hai_data.generator.defaults.post_category)
                    );
                });
                
                this.toggleCategoryWrapper(true);
            } else {
                this.toggleCategoryWrapper(false);
            }
        },

        initAjaxSubmitHandler: function() {
            const $form = $('#hai-options-form');
            
            $form.on('submit', () => {
                this.setButtonState(true);
            });
        },

        initRemindConnectionNotice: function() {
            $('.hai-no-connection-notice .close-remind-notice').on('click', function(e) {
                e.preventDefault();

                const button = $(this);

                $.ajax({
                    url: hai_data.ajax_url,
                    type: 'post',
                    dataType: 'json',
                    data: {
                        action: 'hai_dismiss_remind_notice',
                        nonce: hai_data.nonce
                    },
                    beforeSend: function(){
                        button.addClass('disabled');
                    },
                    success: response => {
                        if(response?.success){
                            button.closest('.hai-no-connection-notice').fadeOut();
                        } else {
                            button.removeClass('disabled');
                        }
                    },
                    error: e => {
                        button.removeClass('disabled');
                    }
                });
            });
        }
    };

    HaiAdmin.init();
});