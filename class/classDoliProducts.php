    // container has a certain width
                        // make selection container a bit wider
                        selectionContainerWidth = selectionContainerWidth * 1.2;
                    } else {

                        var borderRadiusSelector = displayContainerAboveInput ? 'border-bottom-right-radius' : 'border-top-right-radius';

                        // no border radius on top
                        this.$selectionContainer
                            .css(borderRadiusSelector, 'initial');

                        if (this.$actionButtons) {
                            this.$actionButtons
                                .css(borderRadiusSelector, 'initial');
                        }
                    }

                    this.$selectionContainer
                        .css('top', Math.floor(selectionContainerYPos))
                        .css('left', Math.floor(this.$container.offset().left))
                        .css('width', selectionContainerWidth);

                    // remember the position
                    this.config.displayContainerAboveInput = displayContainerAboveInput;
                }
            },

            selectAllMaxItemsThreshold: 30,
            showSelectAll: function () {
                return this.config.multiple && this.config.selectAllMaxItemsThreshold && this.items && this.items.length <= this.config.selectAllMaxItemsThreshold;
            },

            useBracketParameters: false,
            multiple: undefined,
            showSelectionBelowList: false,
            allowNullSelection: false,
            scrollTarget: undefined,
            maxHeight: undefined,
            converter: undefined,
            asyncBatchSize: 300,
            maxShow: 0
        },

        // initialize the plugin
        init: function () {
            this.config = $.extend(true, {}, this.defaults, this.options, this.metadata);

            var originalName = this._getNameAttribute(),
                sol = this;

            if (!originalName) {
                this._showErrorLabel('name attribute is required');
                return;
            }

            // old IE does not support trim
            if (typeof String.prototype.trim !== 'function') {
                String.prototype.trim = function () {
                    return this.replace(/^\s+|\s+$/g, '');
                }
            }

            this.config.multiple = this.config.multiple || this.$o