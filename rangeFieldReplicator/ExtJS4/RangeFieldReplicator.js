/**
 * A component with list of ranges
 * @class App.component.form.RangeFieldReplicator
 * @extends Ext.container.Container
 */
Ext.define('App.component.form.RangeFieldReplicator', {
    extend: 'Ext.container.Container',
    alias: 'widget.rangefieldreplicator',

    config: {
        /**
         * @cfg {boolean} allowBlank Whether empty list is allowed value
         */
        allowBlank: true,

        /**
         * @cfg {string} invalidText Text for field with invalid value
         */
        invalidText: '',

        /**
         * @cfg itemContainerConfig Config object for creating fields
         */
        itemContainerConfig: {},

        /**
         * @cfg {Object[]} fieldConfigs Array of object to configure fields
         */
        fieldConfigs: [],

        /**
         * @cfg {number} fieldNumber Number of fields to be created in range.
         * Used when fieldConfigs is empty.
         */
        fieldNumber: 2,

        /**
         * @cfg {string}
         */
        fieldXType: 'textfield',

        /**
         * @cfg {Object} separatorConfig An object to configure the component that separates the fields
         */
        separatorConfig: {}
    },

    layout: 'anchor',

    /**
     * @inheritDoc
     */
    initComponent: function() {
        var me = this,
            fieldConfigs = [],
            createFieldCfgFn,
            rangeItems = [],
            i;

        createFieldCfgFn = function (options) {
            return Ext.apply(
                {
                    flex: 1,
                    listeners: {
                        scope: me,
                        blur: me.onChange,
                        change: me.onChange
                    },
                    validator: me.validatorBlank
                },
                options,
                {
                    xtype: me.fieldXType
                }
            );
        };

        if (!Ext.isEmpty(this.fieldConfigs)) {
            fieldConfigs = this.fieldConfigs;
        } else if (this.fieldNumber) {
            for (i = 0; i < this.fieldNumber; i++) {
                fieldConfigs.push({});
            }
        }
        fieldConfigs = Ext.Array.map(fieldConfigs, createFieldCfgFn, this);

        // Add separator to fields
        Ext.Array.forEach(fieldConfigs, function (config, index, allConfigs) {
            rangeItems.push(config);
            if (index !== allConfigs.length - 1) {
                rangeItems.push(this.separatorConfig);
            }
        }, this);

        this.items = [
            Ext.apply({
                xtype: 'container',
                anchor: '100%',
                layout: 'hbox',
                border: 0,
                items: [
                    {
                        xtype: 'fieldcontainer',
                        layout: 'hbox',
                        flex: 1,
                        items: rangeItems
                    },
                    {
                        xtype: 'button',
                        cls: 'range-replicator-button-remove',
                        margin: '0 0 0 10',
                        width: 32,
                        handler: this.onClickRemoveRange
                    }
                ]
            }, this.itemContainerConfig)
        ];

        this.callParent(arguments);

        this.inintialRangeContainer = this.down('container');
        if (this.inintialRangeContainer && !this.inintialRangeContainer.replicatorId) {
            this.inintialRangeContainer.replicatorId = Ext.id(this.getEl());
        }
    },

    /**
     *
     * @param value
     * @returns {*}
     */
    validatorBlank: function (value) {
        var field = this,
            rangeContainer = field.up('[replicatorId]'),
            me = rangeContainer.up('rangefieldreplicator'),
            replicatedRangeContainers,
            fields,
            filledCount = 0;

        if (me.allowBlank || !me.isVisible(true)) {
            return true;
        }

        replicatedRangeContainers = me.getReplicatedRangeContainers(rangeContainer);

        // If it is more then one container then the last can pass the validation
        if (replicatedRangeContainers.length > 1) {
            fields = me.getRangeFields(rangeContainer);
            filledCount = me.getFilledFieldsCount(fields);
            if (!filledCount) {
                return true;
            }
        }

        if (Ext.isEmpty(value)) {
            return me.invalidText || field.invalidText;
        }

        return true;
    },

    /**
     * Gets the list of fields in range container
     * @param {Ext.container.Container} container
     * @returns {Ext.Component[]|Ext.util.MixedCollection}
     */
    getRangeFields: function (container) {
        return container.query(this.fieldXType);
    },

    getReplicatedRangeContainers: function (rangeContainer) {
        return this.query('[replicatorId=' + rangeContainer.replicatorId + ']');
    },

    isLastRange: function (all, item) {
        return all[all.length - 1] === item;
    },

    /**
     *
     * @param fields
     * @param {boolean} [checkValid]
     * @returns {number}
     */
    getFilledFieldsCount: function (fields, checkValid) {
        var count = 0;

        Ext.each(fields, function (field) {
            if (
                !Ext.isEmpty(field.getRawValue()) &&
                (!checkValid || field.isValid())
            ) {
                count++;
            }
        });

        return count;
    },

    /**
     * Handles change event on field
     * @param {Ext.form.field.Text} field
     */
    onChange: function(field) {
        var me = this,
            rangeContainer = field.up('[replicatorId]'),
            fields = this.getRangeFields(rangeContainer),
            filledCount = me.getFilledFieldsCount(fields),
            filledValidCount = 0,
            siblings = this.getReplicatedRangeContainers(rangeContainer),
            isLastInGroup = me.isLastRange(siblings, rangeContainer);

        // If a range before the final one was blanked out, remove it
        if (!filledCount && !isLastInGroup) {
            Ext.Function.defer(rangeContainer.destroy, 10, rangeContainer); //delay to allow tab key to move focus first
        }
        // If the range is the last in the list and has all valid values, add a cloned range after it
        else if (filledCount && isLastInGroup) {
            this.setRangeRemoveButtonVisibility(rangeContainer, true);
            filledValidCount = me.getFilledFieldsCount(fields, true);
            if (filledValidCount === fields.length) {
                if (rangeContainer.onReplicate) {
                    rangeContainer.onReplicate();
                }
                me.addCloneRange();
            }
        }

        me.fireEvent('change', this, this.getValue());
    },

    /**
     * Sets the remove button visibility
     * @param {Ext.container.Container} rangeContainer
     * @param {boolean} isVisible
     */
    setRangeRemoveButtonVisibility: function (rangeContainer, isVisible) {
        var button = rangeContainer.down('button');

        if (isVisible) {
            button.removeCls('range-replicator-button-remove-hidden');
        } else {
            button.addCls('range-replicator-button-remove-hidden');
        }
    },

    /**
     * Handles click on remove button
     * @param {Ext.button.Button} button
     */
    onClickRemoveRange: function (button) {
        var rangeContainer = button.up('container'),
            me = rangeContainer.up('rangefieldreplicator');

        me.removeRange(rangeContainer);
    },

    /**
     * Removes a range container.
     * If it is single then clears a values.
     * @param {Ext.container.Container} rangeContainer
     */
    removeRange: function (rangeContainer) {
        var fields;

        // Remove except last
        if (this.items.length > 1 && this.items.indexOf(rangeContainer) !== this.items.length - 1) {
            this.remove(rangeContainer);
        } else {
            // If single or last then clear values
            fields = this.getRangeFields(rangeContainer);
            Ext.each(fields, function (field) {
                field.setValue('');
            });
            this.setRangeRemoveButtonVisibility(rangeContainer, false);
        }
        // Focus first field to process validation on blur
        this.items.first().down(this.fieldXType).focus();
    },

    /**
     * Adds a new cloned range container
     * @param {Array} [rangeValues] Array of range values
     */
    addCloneRange: function (rangeValues) {
        var rangeContainer = this.inintialRangeContainer,
            replicatorId = rangeContainer.replicatorId,
            clonedRange,
            fields;

        clonedRange = rangeContainer.cloneConfig({replicatorId: replicatorId});
        fields = this.getRangeFields(clonedRange);
        rangeValues = rangeValues || [];
        if (rangeValues.length === fields.length) {
            Ext.each(fields, function (field, index) {
                field.setRawValue(rangeValues[index]);
            });
        } else {
            this.setRangeRemoveButtonVisibility(clonedRange, false);
        }
        this.suspendLayouts();
        this.add(clonedRange);
        this.resumeLayouts();
        this.updateLayout();
    },

    /**
     * Sets values for the list
     * @param {Object[]} values
     */
    setValue: function(values) {
        var i;

        this.removeAll();
        if (Ext.isArray(values)) {
            for (i = 0; i < values.length; i++) {
                if (!Ext.isEmpty(values[i])) {
                    this.addCloneRange(values[i]);
                }
            }
        }

        // Empty one
        this.addCloneRange();
    },

    /**
     * Returns values of the list
     * @returns {Object[]}
     */
    getValue: function() {
        var result = [];

        this.items.each(function(rangeContainer) {
            var fields = this.getRangeFields(rangeContainer),
                filledCount = this.getFilledFieldsCount(fields),
                values = [];

            if (filledCount && filledCount === fields.length) {
                values = Ext.Array.map(fields, function (field) {
                    return field.getValue();
                });
                result.push(values);
            }
        }, this);

        return result;
    }
});
