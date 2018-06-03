function vSectionMenuInit() {
    var appSectionMenu;
    var sectionMenuContainer = document.getElementsByClassName('js-section-tree')[0];

    Vue.component('section-menu-item', {
        props: [
            'menu',
            'selected',
            'canedit'
        ],

        template: [
            '<div class="section-menu-item">',
                '<div class="section-menu-item__arrows" v-if="canedit">',
                    '<div :title="getMoveUpTitle" @click.prevent="moveUp"><i class="fa fa fa-chevron-up section-menu__arrow-up"></i></div>',
                    '<div :title="getMoveDownTitle" @click.prevent="moveDown"><i class="fa fa fa-chevron-down section-menu__arrow-down"></i></div>',
                '</div>',
                '<a class="section-menu__link" :href="menu.url" @click="clickSection">',
                    '<div v-if="!menu.isHidden" class="section-menu__name" :class="getNameClass" :style="getNameStyle">',
                        '<span v-if="!this.menu.isLeaf" class="section-menu__name-arrow" @click.prevent="toggleSection">',
                            '<i v-if="this.menu.isOpened" class="fa fa-caret-down" title="Свернуть"></i>',
                            '<i v-else class="fa fa-caret-right" title="Развернуть"></i>',
                        '</span>',
                        '<span :class="getModClass">',
                            '<i v-if="this.menu.isMod" class="fa fa-lock"></i>',
                            '{{ menu.name }}',
                        '</span>',
                    '</div>',
                '</a>',
            '</div>'
        ].join("\n"),

        computed: {
            getMoveUpTitle: function () {
                return 'Переместить вверх "' + this.menu.name + '"';
            },
            getMoveDownTitle: function () {
                return 'Переместить вниз "' + this.menu.name + '"';
            },
            getLeftIndent: function () {
                return (this.menu.level) * 8;
            },
            getNameClass: function () {
                return {
                    'section-has-children': !this.menu.isLeaf,
                    'section-first': this.menu.level === 1 && !this.menu.isLeaf,
                    'section-last': this.menu.isLeaf,
                    'section-selected': this.menu.id === this.selected
                }
            },
            getNameStyle: function () {
                return {
                    'padding-left': this.getLeftIndent + 'px'
                };
            },
            getModClass: function () {
                return {
                    'section-menu__name-leaf': this.menu.isLeaf
                }
            }
        },

        methods: {
            toggleSection: function () {
                this.menu.isOpened = !this.menu.isOpened;
                //пройтись по по списку и спрятать разделы, у которых parentId = id и их потомков
                this.$emit('togglechildren', this.menu.id, this.menu.isOpened);
            },
            moveUp: function () {
                this.$emit('moveup', this.menu.id);
            },
            moveDown: function () {
                this.$emit('movedown', this.menu.id);
            },
            clickSection: function () {
                this.$emit('savescroll');
            }
        }
    });

    appSectionMenu = new Vue({
        el: '.app-section-menu-desktop',
        data: {
            isLoading: false,
            filterText: '',
            //TODO сделать не наблюдаемым?
            tree: [],
            list: [],
            selectedId: catID,
            forumType: forumType,
            isFiltered: false,
            canEdit: canEdit
        },

        created: function () {
            var context = this;

            this.showLoading();
            /*axios.post('/api/sections-get/', {
                forumType: this.forumType,
                selectedId: this.selectedId
            })
            .then(function (response) {
                context.tree = response.data.tree;
                applyArray(context.list, makeListFromTree(context.tree), context.hideLoading);

                if (context.selectedId) {
                    localforage.getItem('scrollTop_' + context.forumType).then(function (value) {
                        sectionMenuContainer.scrollTop = value;
                    });
                }
            })
            .catch(function (error) {
                console.log(error);
            });*/

            context.tree = sectionTree.tree;
            applyArray(context.list, makeListFromTree(context.tree), context.hideLoading);

            if (context.selectedId) {
                localforage.getItem('scrollTop_' + context.forumType).then(function (value) {
                    sectionMenuContainer.scrollTop = value;
                });
            }
        },

        computed: {
            debouncedFilterSections: function() {
                return debounce(this.filterSections, 1000);
            }
        },

        methods: {
            showLoading: function () {
                this.isLoading = true;
            },
            hideLoading: function () {
                this.isLoading = false;
            },
            filterSections: function () {
                this.showLoading();

                var filterText = this.filterText.toLowerCase(),
                    tree = deepCopy(this.tree);

                this.isFiltered = false;
                if (filterText) {
                    tree = filterTree(tree, filterText);
                    this.isFiltered = true;
                }

                applyArray(this.list, makeListFromTree(tree), this.hideLoading);
            },
            clearFilter: function () {
                this.filterText = '';
                this.filterSections();
            },
            toggleSectionChildren: function (parentId, isOpened) {
                // isOpened - новое состояние

                var toggleIds = [parentId];
                var closedIds = [];

                //TODO все чайлды
                var newList = this.list.map(function (item) {
                    if (toggleIds.indexOf(item.parentId) !== -1) {
                        if (toggleIds.indexOf(item.id) === -1) {
                            toggleIds.push(item.id);
                        }

                        item.isHidden = !isOpened;

                        // Если узел был закрыт, то его потомки должны быть скрыты (в следующих итерациях)
                        if (isOpened && !item.isOpened && closedIds.indexOf(item.id) === -1) {
                            closedIds.push(item.id)
                        }

                        if (closedIds.indexOf(item.parentId) !== -1) {
                            item.isHidden = true;
                        }
                    }

                    return item;
                });

                applyArray(this.list, newList);
            },
            moveSection: function (direction, menuId) {
                var context = this,
                    methodName;

                switch (direction) {
                    case 'up':
                        methodName = '/api/section-move-up/';
                        break;

                    case 'down':
                        methodName = '/api/section-move-down/';
                        break;
                }

                if (!methodName) {
                    return;
                }

                //TODO отрефакторить
                axios.post(
                    methodName, {
                        id: menuId,
                        forumType: context.forumType,
                        selectedId: context.selectedId
                    }
                )
                .then(function (response) {
                    context.tree = response.data.tree;
                    applyArray(context.list, makeListFromTree(context.tree));
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            moveSectionUp: function (menuId) {
                this.moveSection('up', menuId);
            },
            moveSectionDown: function (menuId) {
                this.moveSection('down', menuId);
            },
            saveScroll: function () {
                localforage.setItem('scrollTop_' + this.forumType, sectionMenuContainer.scrollTop);
            }
        }
    });
}