/**
 * The component displays options with states as a table
 * @class App.components.dataview.StateMatrix
 * @extends Ext.Component
 */
Ext.define('App.components.dataview.StateMatrix', {
    extend: 'Ext.Component',
    alias: [
        'widget.statematrix',
        'widget.statetable'
    ],

    width: 480,
    height: 280,

    /**
     * @example
     *      axisX: [
     *          {
     *              value: 0,
     *              title: 'x0'
     *          },
     *          ...
     *      ],
     *      axisY: [
     *          {
     *              value: 0,
     *              title: 'y0'
     *          },
     *          ...
     *      ],
     *      cellStates: [
     *          {
     *              x: 0,
     *              y: 0,
     *              state: 'state1'
     *          },
     *          ...
     *      ],
     *      stateList: {
     *          'state1': {
     *              next: 'state2',
     *              cls: 'state1-color',
     *              clsSelected: 'state1-color-selected'
     *          },
     *          'state2': {
     *              next: 'state1',
     *              cls: 'state1-color',
     *              clsSelected: 'state1-color-selected'
     *          },
     *          ...
     *      },
     *      defaultState: 'state1'
     *
     */
    config: {
        axisX: [],
        axisY: [],
        cellStates: [],
        stateList: {},
        defaultState: null,
        is24H: true
    },

    data: {},

    /**
     * @inheritDoc
     */
    constructor: function (options) {
        this.initConfig(options);
        this.callParent(arguments);
    },

    /**
     * @inheritDoc
     */
    initComponent: function () {
        // Object to save data for selected cells
        this.selection = {
            area: null,
            renderedCount: 0,
            coords: {},
            isProcessing: false
        };
        // The element with state selector panel
        this.selectorEl = null;
        // Object to save data for cells
        this.cellCache = {};

        var axisXTpl = this.createAxisXTpl(),
            axisYTpl = this.createAxisYTpl(),
            tableTpl = this.createTableTpl(),
            selectorTpl = this.createSelectorTpl(),
            componentTpl = [
                '<div class="state-matrix">',
                    '<div class="state-matrix-left">',
                        '<div class="axis-y">' + axisYTpl + '</div>',
                    '</div>',
                    '<div class="state-matrix-right">',
                        '<div class="axis-x">' + axisXTpl + '</div>',
                        tableTpl,
                        selectorTpl,
                    '</div>',
                '</div>'
            ];

        this.tpl = new Ext.XTemplate(componentTpl);

        this.callParent(arguments);
    },

    /**
     * Creates template string for axis-X
     * @returns {string}
     */
    createAxisXTpl: function () {
        //TODO to XTemplates?
        var x,
            maxX = this.axisX.length,
            noon,
            noonIcoClass,
            title,
            tpl = '<div class="division-x-wrap">';

        for (x = 0; x < maxX; x++) {
            noon = '';
            noonIcoClass = '';
            if (x === 0) {
                if (this.is24H) {
                    noonIcoClass = 'division-x-moon-svg';
                } else {
                    noon = 'AM';
                }
            } else if (x === 12) {
                if (this.is24H) {
                    noonIcoClass = 'division-x-sun-svg';
                } else {
                    noon = 'PM';
                }
            }
            title = this.axisX[x].title;

            tpl += '<div class="division-x-outer">';
            tpl += '<div class="division-x-dash"></div>';
            if (x === maxX - 1) {
                noon = '';
                noonIcoClass = '';

                if (this.is24H) {
                    noonIcoClass = 'division-x-moon-svg';
                    title = '24';
                } else {
                    noon = 'AM';
                    title = '00';
                }

                tpl += '' +
                    '<div class="division-x-noon-last ' + noonIcoClass + '">' + noon + '</div>' +
                    '<div class="division-x-title-last">' + title + '</div>' +
                    '<div class="division-x-dash-last"></div>';

            } else {
                tpl += '' +
                    '<div class="division-x-noon ' + noonIcoClass + '">' + noon + '</div>' +
                    '<div class="division-x-title">' + title + '</div>';

            }
            tpl += '</div>';
        }
        tpl += '</div>';

        return tpl;
    },

    /**
     * Creates template string for axis-Y
     * @returns {string}
     */
    createAxisYTpl: function () {
        var y,
            maxY = this.axisY.length,
            tpl = '<div class="division-y-wrap">';

        for (y = 0; y < maxY; y++) {
            tpl += '' +
                '<div class="division-y-outer">' +
                    '<div class="division-y-inner">' + this.axisY[y].title + '</div>' +
                '</div>';
        }
        tpl += '</div>';

        return tpl;
    },

    /**
     * Creates template string for table
     * @returns {string}
     */
    createTableTpl: function () {
        var x, y,
            maxX = this.axisX.length,
            maxY = this.axisY.length,
            cell,
            coord,
            tooltipHourStart,
            tooltipHourEnd,
            tooltip,
            defaultState = (this.defaultState && this.stateList.hasOwnProperty(this.defaultState)) ? this.defaultState : Object.keys(this.stateList)[0],
            defaultStateCls = this.getStateCls(defaultState),
            tpl = '<div class="table">';

        for (y = 0; y < maxY; y++) {
            tpl += '<div class="row">';
            for (x = 0; x < maxX; x++) {
                cell = this.makeCell(x, y);
                coord = cell.toString();

                // Create object for each cell
                this.cellCache[coord] = {
                    el: null,
                    cell: cell,
                    x: x,
                    y: y,
                    state: defaultState
                };

                if (this.is24H) {
                    tooltipHourStart = x + ':00';
                    tooltipHourEnd = (x + 1) + ':00';
                } else {
                    tooltipHourStart = this.get12HourFormat(x);
                    tooltipHourEnd = this.get12HourFormat(x + 1);
                }
                tooltip = tooltipHourStart + ' - ' + tooltipHourEnd + ', ' + this.axisY[y].title;

                tpl += '<div title="' + tooltip + '" class="cell ' + defaultStateCls + '" data-coord="' + coord + '" data-state="' + defaultState + '"></div>';
            }
            tpl += '</div>';
        }
        tpl += '</div>';

        return tpl;
    },

    /**
     * Creates template string for items in selector panel
     * @returns {string}
     */
    createSelectorItemsTpl: function () {
        var stateKey,
            tpl = '';

        for (stateKey in this.stateList) {
            if (this.stateList.hasOwnProperty(stateKey)) {
                tpl += '<div class="selector-item-wrap"><div class="selector-item ' + this.getStateCls(stateKey) + '" data-state="' + stateKey + '"></div></div>';
            }
        }

        return tpl;
    },

    /**
     * Creates template string for selector panel
     * @returns {string}
     */
    createSelectorTpl: function () {
        return '<div class="selector">' + this.createSelectorItemsTpl() + '</div>';
    },

    /**
     * Updates selector panel. It is called after setStateList.
     */
    updateStateList: function() {
        if (this.selectorEl) {
            this.selectorEl.update(this.createSelectorItemsTpl());
            this.updateSelectorWidth();
        }
    },

    /**
     * Updates selector panel width
     */
    updateSelectorWidth: function () {
        var selectorItemEl = this.getElByQuery('.selector-item-wrap', this.selectorEl.dom),
            selectorItemWidth = selectorItemEl.getComputedWidth(),
            //TODO
            selectorItemCount = Object.keys(this.stateList).length;

        this.selectorEl.setWidth(selectorItemWidth * selectorItemCount + 10);
    },

    /**
     * Converts hour to locale format
     * @param {number} h hours
     * @returns {string}
     */
    get12HourFormat: function (h) {
        return (h === 12 ? h : h % 12) + ((h <= 12) ? 'am' : 'pm');
    },

    /**
     * Gets element by query selector
     * @param {string} query
     * @param [rootDomEl]
     * @returns {Ext.dom.Element}
     */
    getElByQuery: function (query, rootDomEl) {
        return Ext.get(Ext.dom.Query.selectNode(query, rootDomEl ? rootDomEl : this.getEl().dom));
    },

    /**
     * @inheritDoc
     */
    afterRender: function() {
        this.callParent(arguments);

        var matrixEl = this.getEl(),
            axisXEl = this.getElByQuery('.axis-x'),
            tableEl = this.getElByQuery('.table');

        tableEl.setHeight(matrixEl.getHeight() - axisXEl.getHeight());

        this.selectorEl = this.getElByQuery('.selector');
        this.updateSelectorWidth();
        this.hideSelectorEl();

        // Events
        matrixEl.on({
            'dragstart': {
                fn: Ext.emptyFn,
                stopEvent : true
            },
            'mousedown': {
                fn: this.startSelection,
                delegate: '.cell',
                scope: this
            },
            'mouseup': {
                fn: this.mouseupHandler,
                scope: this
            },
            'mouseleave': {
                fn: this.cancelSelection,
                scope: this
            }
        });

        this.selectorEl.on({
            'click': {
                fn: this.selectState,
                delegate: '.selector-item',
                scope: this
            },
            'mouseover': {
                fn: this.hoverSelector,
                delegate: '.selector-item',
                scope: this
            },
            'mouseout': {
                fn: this.hoverSelector,
                delegate: '.selector-item',
                scope: this
            }
        });
    },

    /**
     * Called after setCellStates.
     * Applies importing data to component.
     * @param {Object[]} data
     */
    updateCellStates: function (data) {
        var el,
            coord,
            newState,
            i, x, y;

        if (Ext.isEmpty(data)) {
            return;
        }

        for (i = 0; i < data.length; i++) {
            x = data[i].x;
            y = data[i].y;
            newState = data[i].state;

            coord = this.getCoordString(x, y);

            this.cellCache[coord].x = x;
            this.cellCache[coord].y = y;
            this.cellCache[coord].state = newState;

            el = this.getCellDomEl(coord);
            if (el) {
                this.removeAllStateCls(el);

                this.setStateToEl(el, newState);
                this.addStateCls(el, newState);
            }
        }

        this.fireEvent('savestates', this, this.cellStates);
    },

    /**
     * Removes css-classes for all states from element
     * @param {Ext.dom.Element} el
     */
    removeAllStateCls: function (el) {
        el.dom.className = 'cell';
    },

    /**
     * Removes selected css-class from element
     * @param {Ext.dom.Element} el
     */
    removeSelectedCls: function (el, state) {
        el.removeCls(this.getSelectedCls(state));
    },

    /**
     * Adds state css-class to element
     * @param {Ext.dom.Element} el
     */
    addStateCls: function (el, state) {
        el.addCls(this.getStateCls(state));
    },

    /**
     * Adds selected css-class to element
     * @param {Ext.dom.Element} el
     */
    addSelectedCls: function (el, state) {
        el.addCls(this.getSelectedCls(state));
    },

    /**
     * Gets css class name for state
     * @param {?string} state
     */
    getStateCls: function (state) {
        return this.stateList[state] && this.stateList[state].cls;
    },

    /**
     * Gets css class name for selected state
     * @param {?string} state
     */
    getSelectedCls: function (state) {
        return this.stateList[state] && this.stateList[state].clsSelected;
    },

    // Pairs

    /**
     * Pair constructor
     * @param {*} x
     * @param {*} y
     * @returns {Function}
     */
    cons: function (x, y) {
        return function (pick) {
            return pick(x, y);
        };
    },

    /**
     * Gets first element from pair
     * @param {Function} fn
     * @returns {*}
     */
    car: function (fn) {
        return fn(function (x, y) {
            return x;
        });
    },

    /**
     * Gets second element from pair
     * @param {Function} fn
     * @returns {*}
     */
    cdr: function (fn) {
        return fn(function (x, y) {
            return y;
        });
    },

    // Cell, Area constructors

    /**
     * Gets coordinates
     * @param {(string|number)} x
     * @param {(string|number)} y
     * @returns {string}
     */
    getCoordString: function (x, y) {
        return x + ':' + y;
    },

    /**
     * Cell constructor
     * @param {Number} x
     * @param {Number} y
     * @returns {*|Function}
     */
    makeCell: function (x, y) {
        var me = this,
            fn = this.cons(x, y);

        fn.toString = function () {
            return me.getCoordString(me.getX(fn), me.getY(fn));
        };

        return fn;
    },

    /**
     * @param {*} cell
     * @returns {boolean}
     */
    isCell: function (cell) {
        return Ext.isFunction(cell);
    },

    /**
     * @param {*} cell
     * @returns {boolean}
     */
    isArea: function (area) {
        return Ext.isFunction(area);
    },

    /**
     * @param {*} area
     * @returns {boolean}
     */
    isAreaSingleCell: function (area) {
        var startCell, endCell;
        if (this.isArea(area)) {
            startCell = this.getStartCell(area);
            endCell = this.getEndCell(area);

            if (
                this.getX(startCell) === this.getX(endCell) &&
                this.getY(startCell) === this.getY(endCell)
            ) {
                return true;
            }
        }
    },

    /**
     * Gets X-coordinate from cell
     * @param {Function} fn
     * @returns {(Number|boolean)}
     */
    getX: function (fn) {
        if (!this.isCell(fn)) {
            return false;
        }

        return parseInt(this.car(fn), 10);
    },

    /**
     * Gets Y-coordinate from cell
     * @param fn
     * @returns {(Number|boolean)}
     */
    getY: function (fn) {
        if (!this.isCell(fn)) {
            return false;
        }

        return parseInt(this.cdr(fn), 10);
    },

    /**
     * Area constructor
     * @param {Function} startCell
     * @param {Function} endCell
     * @returns {*|Function}
     */
    makeArea: function (startCell, endCell) {
        if (!this.isCell(startCell) || !this.isCell(endCell)) {
            return false;
        }

        return this.cons(startCell, endCell);
    },

    /**
     * Gets start cell from area
     * @param {Function} area
     * @returns {(Function|boolean)}
     */
    getStartCell: function (area) {
        if (!this.isArea(area)) {
            return false;
        }

        return this.car(area);
    },

    /**
     * Gets end cell from area
     * @param {Function} area
     * @returns {(Function|boolean)}
     */
    getEndCell: function (area) {
        if (!this.isArea(area)) {
            return false;
        }

        return this.cdr(area);
    },

    /**
     * Set start cell to area
     * @param {Function} area
     * @param {Function} startCell
     * @returns {(Function|boolean)}
     */
    setAreaStartCell: function (area, startCell) {
        if (!this.isArea(area) || !this.isCell(startCell)) {
            return false;
        }

        return this.cons(startCell, this.getEndCell(area));
    },

    /**
     * Set end cell to area
     * @param {Function} area
     * @param {Function} endCell
     * @returns {(Function|boolean)}
     */
    setAreaEndCell: function (area, endCell) {
        if (!this.isArea(area) || !this.isCell(endCell)) {
            return false;
        }

        return this.cons(this.getStartCell(area), endCell);
    },

    //<debug>
    /**
     * Prints debug info
     * @param area
     */
    printAreaCoords: function (area) {
        var startCell = this.getStartCell(area),
            endCell = this.getEndCell(area);

        Ext.global.console.log(this.getX(startCell) + ':' + this.getY(startCell), ' -> ', this.getX(endCell) + ':' + this.getY(endCell));
        Ext.global.console.log(this.getAreaCoords(area));
    },
    //</debug>

    // Other functions

    /**
     * Gets HTML element that is binded to coordinates
     * @param {String} coord coordinates in format 'x:y'
     * @returns {HTMLElement}
     */
    getCellDomEl: function (coord) {
        if (!this.cellCache.hasOwnProperty(coord)) {
            this.cellCache[coord] = {};
        }
        if (!this.cellCache[coord].el) {
            this.cellCache[coord].el = this.getElByQuery('[data-coord="' + coord + '"]');
        }

        return this.cellCache[coord].el;
    },

    /**
     * Makes cell from data in html element
     * @param {HTMLElement} el
     * @returns {(Function|boolean)}
     */
    getCellFromDomEl: function (el) {
        var coord = el.getAttribute('data-coord'), 
            coordArr;

        if (!coord) {
            return false;
        }

        if (!this.cellCache.hasOwnProperty(coord)) {
            this.cellCache[coord] = {};
        }
        if (!this.isCell(this.cellCache[coord].cell)) {
            coordArr = coord.split(':');
            this.cellCache[coord].cell = this.makeCell(coordArr[0], coordArr[1]);
        }

        return this.cellCache[coord].cell;
    },

    /**
     * Gets data state from DOM element
     * @param {HTMLElement} el
     * @returns {string}
     */
    getStateFromDomEl: function (el) {
        return el.getAttribute('data-state');
    },

    /**
     * Sets data state to element
     * @param {Ext.dom.Element} el
     * @param {string} state
     */
    setStateToEl: function (el, state) {
        el.dom.setAttribute('data-state', state);
    },

    // Event handlers

    /**
     * Event handler on start a selection
     * @param {Ext.EventObjectImpl} e The observable event
     */
    startSelection: function (e) {
        this.getEl().un('mouseover', this.moveSelection, this);

        if (!Ext.isEmpty(this.selection.coords)) {
            this.clearSelected();
        }

        var cell = this.getCellFromDomEl(e.target);

        if (!this.isCell(cell)) {
            return false;
        }

        this.selection.area = this.makeArea(cell, cell);
        // On left button click start listen to selecting
        if (e.browserEvent.which === 1) {
            this.getEl().on('mouseover', this.moveSelection, this);
        }
    },

    /**
     * Event handler on move a selection
     * @param {Ext.EventObjectImpl} e The observable event
     */
    moveSelection: function (e) {
        var startCell = this.getStartCell(this.selection.area),
            endCell = this.getCellFromDomEl(e.target),
            area = this.makeArea(startCell, endCell);

        if (this.isArea(area)) {
            this.selection.area = area;
            this.renderSelection(this.selection);
        }
    },

    /**
     * @param {Ext.EventObjectImpl} e The observable event
     */
    cancelSelection: function (e) {
        this.getEl().un('mouseover', this.moveSelection, this);
        this.clearSelected();
        this.hideSelectorEl();
    },

    /**
     * Event handler on end a selection
     * @param e The observable event
     */
    stopSelection: function (e) {
        var el = e.target,
            newState,
            coord,
            endCell = this.getCellFromDomEl(el);

        this.getEl().un('mouseover', this.moveSelection, this);

        if (!this.isCell(this.getStartCell(this.selection.area))) {
            return false;
        }

        this.setAreaEndCell(this.selection.area, endCell);

        if (this.isAreaSingleCell(this.selection.area)) {
            newState = this.stateList[this.getStateFromDomEl(el)].next;
            coord = endCell.toString();
            this.changeCellState(coord, newState);
            this.hideSelectorEl();

            this.exportCellStates();
        } else {
            this.renderSelection(this.selection);
            this.selection.renderedCount = 0;
            this.showSelectorEl(e);
        }
    },

    /**
     * @param {Ext.EventObjectImpl} e The observable event
     */
    mouseupHandler: function (e) {
        var classList = e.target.classList;

        if (classList.contains('cell')) {
            this.stopSelection(e);
        } else if (!classList.contains('selector-item')) {
            this.cancelSelection(e);
        }
    },

    /**
     * Shows selector control panel
     * @param {Ext.EventObjectImpl} e
     */
    showSelectorEl: function (e) {
        this.selectorEl.position('absolute', 100, (e.browserEvent.pageX - this.selectorEl.getWidth() / 2), (e.browserEvent.pageY));

        // Fix a position
        if (this.selectorEl.getLocalX() < 0) {
            this.selectorEl.setStyle('left', 0);
        } else if (this.selectorEl.getLocalX() > this.getEl().getWidth() - this.selectorEl.getWidth()) {
            this.selectorEl.setStyle('left', (this.getEl().getWidth() - this.selectorEl.getWidth()) + 'px');
        }

        if (!this.selectorEl.isVisible()) {
            this.selectorEl.show();
        }
    },

    /**
     * Hides selector control panel
     */
    hideSelectorEl: function () {
        if (this.selectorEl.isVisible()) {
            this.selectorEl.hide();
        }
    },

    /**
     *
     * @param {Ext.EventObjectImpl} e The observable event
     */
    hoverSelector: function (e) {
        var el = e.target,
            state = this.getStateFromDomEl(el),
            selectedCls = this.getSelectedCls(state);

        if (el.classList.contains(selectedCls)) {
            el.classList.remove(selectedCls);
        } else {
            el.classList.add(selectedCls);
        }
    },

    /**
     * @param coord
     * @param newState
     */
    changeCellState: function (coord, newState) {
        var el = this.getCellDomEl(coord),
            curState;

        if (el) {
            curState = this.getStateFromDomEl(el);
            this.removeSelectedCls(el, curState);
            delete this.selection.coords[coord];

            this.removeAllStateCls(el);

            this.setStateToEl(el, newState);
            this.addStateCls(el, newState);
        }

        if (!this.cellCache.hasOwnProperty(coord)) {
            this.cellCache[coord] = {};
        }
        this.cellCache[coord].state = newState;
    },

    /**
     * Sets new state to selected area
     * @param {Ext.EventObjectImpl} e The observable event
     */
    selectState: function (e) {
        var target = e.target,
            newState,
            coord;

        this.selection.isProcessing = true;
        for (coord in this.selection.coords) {
            if (!this.selection.coords.hasOwnProperty(coord)) {
                continue;
            }

            newState = this.getStateFromDomEl(target);
            if (newState) {
                this.changeCellState(coord, newState);
            }
        }
        this.selection.area = null;
        this.selection.isProcessing = false;

        this.hideSelectorEl();

        // Update cell data and fire save event
        this.exportCellStates();
    },

    /**
     * Removes selected area
     */
    clearSelected: function () {
        var el,
            state,
            coord;

        if (this.selection.isProcessing) {
            return;
        }

        this.selection.isProcessing = true;
        for (coord in this.selection.coords) {
            if (!this.selection.coords.hasOwnProperty(coord)) {
                continue;
            }

            el = this.getCellDomEl(coord);
            if (el) {
                state = this.getStateFromDomEl(el);
                this.removeSelectedCls(el, state);
            }

            delete this.selection.coords[coord];
        }
        this.selection.area = null;
        this.selection.isProcessing = false;
    },

    /**
     * Gets left top and bottom right cell coordinates from area
     * @param area
     * @returns {{topLeftX: Number, topLeftY: Number, bottomRightX: Number, bottomRightY: Number}}
     */
    getAreaCoords: function (area) {
        var startCell = this.getStartCell(area),
            endCell = this.getEndCell(area),
            minX = this.getX(startCell),
            minY = this.getY(startCell),
            maxX = this.getX(endCell),
            maxY = this.getY(endCell),
            tmp;

        if (minX > maxX) {
            tmp = minX;
            minX = maxX;
            maxX = tmp;
        }

        if (minY > maxY) {
            tmp = minY;
            minY = maxY;
            maxY = tmp;
        }

        return {
            topLeftX: minX,
            topLeftY: minY,
            bottomRightX: maxX,
            bottomRightY: maxY
        };
    },

    /**
     * Renders selection area
     * @param {Object} selection
     */
    renderSelection: function (selection) {
        var coords = this.getAreaCoords(selection.area),
            coord, x, y, el, curState;

        //<debug>
        //this.printAreaCoords(selection.area);
        //</debug>

        selection.renderedCount++;
        // Select current selection
        for (x = coords.topLeftX; x <= coords.bottomRightX; x++) {
            for (y = coords.topLeftY; y <= coords.bottomRightY; y++) {
                coord = this.getCoordString(x, y);

                selection.coords[coord] = selection.renderedCount;

                el = this.getCellDomEl(coord);
                if (el) {
                    curState = this.getStateFromDomEl(el);
                    this.addSelectedCls(el, curState);
                }
            }
        }

        // Unselect previous selection
        for (coord in selection.coords) {
            if (selection.coords.hasOwnProperty(coord) && selection.coords[coord] !== selection.renderedCount) {
                el = this.getCellDomEl(coord);
                if (el) {
                    curState = this.getStateFromDomEl(el);
                    this.removeSelectedCls(el, curState);
                }
                delete this.selection.coords[coord];
            }
        }
    },

    /**
     * Converts component cell states to out format
     */
    exportCellStates: function () {
        var coord, item;

        this.cellStates = [];
        for (coord in this.cellCache) {
            if (this.cellCache.hasOwnProperty(coord) && coord) {
                item = this.cellCache[coord];

                this.cellStates.push({
                    x: item.x,
                    y: item.y,
                    state: item.state
                });
            }
        }

        this.fireEvent('savestates');
    }
});
