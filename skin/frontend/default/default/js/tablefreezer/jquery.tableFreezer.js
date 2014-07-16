/*!
 * Table Freeze plugin
 * 
 * Author: Bartosz Hejman
 * Date: 15 June 2014
 *
 */
(function($) {
    $.fn.extend({
        tableFreezer: function(options) {
            var cfg = $.extend(true, {
                parent: '#container',
                freezeHeader: true,
                freezeFooter: false,
                freezeColumnsLeft: false,  // provide integer as a number of columns you want to freeze on the left hand side
                freezeColumnsRight: false,  // provide integer as a number of columns you want to freeze on the right hand side
                css:{
                    cellBorder: '1px solid #CCC',
                    footerBgColor: "#EEE"
                }
            }, options);

            this.each(function() {

                var $table = $(this);

                imagesLoaded( cfg.parent, function() {
                    init($table);
                });
            });

            function buildRows($sourceRow, sourceCellElem, elemClass, rowClass, cellClass){

                $elem = $('<div>').addClass(elemClass);

//                    if(cfg.css.footerBgColor){
//                        $footer.css('background', cfg.css.footerBgColor);
//                    }

                $sourceRow.each(function(){

                    $orgRow = $(this);
                    $row = $('<div>').addClass(rowClass).addClass('clearfix');

                    $elem.append($row);

                    $elem.css('border', cfg.css.cellBorder);

                    $orgRow.find(sourceCellElem).each(function(i){

                        $orgCell = $(this);

                        var w = $orgCell.outerWidth();
                        var h = $orgCell.outerHeight();
                        var paddingLeft = $orgCell.css('padding-left');
                        var paddingRight = $orgCell.css('padding-right');
                        var paddingTop = $orgCell.css('padding-top');
                        var paddingBottom = $orgCell.css('padding-bottom');
                        var html = $orgCell.html();
                        var $cell = $('<div>').addClass(cellClass).html(html);

                        $($cell).css('width', (w) + 'px')
                            .css('height', (h) + 'px')
                            .css('padding-left', paddingLeft)
                            .css('padding-right', paddingRight)
                            .css('padding-top', paddingTop)
                            .css('padding-bottom', paddingBottom)
                            .css('float', 'left')
                            .css('border-right', cfg.css.cellBorder)
                            .css('border-right:last-of-type', 'none')
                            .css('border-bottom', cfg.css.cellBorder)
                            .css('border-bottom:last-of-type', 'none');

                        $row.append($cell);
                    });


                });
                return $elem;

            }

            function buildColumns($sourceRow, sourceCellElem, elemClass, rowClass, cellClass){

                $elem = $('<div>').addClass(elemClass);

//                    if(cfg.css.footerBgColor){
//                        $footer.css('background', cfg.css.footerBgColor);
//                    }

                $sourceRow.each(function(){

                    $orgRow = $(this);
                    $row = $('<div>').addClass(rowClass).addClass('clearfix');

                    $elem.append($row);

                    $elem.css('border', cfg.css.cellBorder);

                    $orgRow.find(sourceCellElem).each(function(i){

                        if(i === cfg.freezeColumnsLeft){
                            return false;
                        }

                        $orgCell = $(this);

                        var w = $orgCell.outerWidth();
                        var h = $orgCell.outerHeight();

                        var paddingLeft = $orgCell.css('padding-left');
                        var paddingRight = $orgCell.css('padding-right');
                        var paddingTop = $orgCell.css('padding-top');
                        var paddingBottom = $orgCell.css('padding-bottom');
                        var html = $orgCell.html();
                        var $cell = $('<div>').addClass(cellClass).html(html);

                        $($cell).css('width', (w) + 'px')
                            .css('height', (h) + 'px')
                            .css('padding-left', paddingLeft)
                            .css('padding-right', paddingRight)
                            .css('padding-top', paddingTop)
                            .css('padding-bottom', paddingBottom)
                            .css('float', 'left')
                            .css('border-right', cfg.css.cellBorder)
                            .css('border-right:last-of-type', 'none')
                            .css('border-bottom', cfg.css.cellBorder)
                            .css('border-bottom:last-of-type', 'none');

                        $row.append($cell);
                    });


                });
                return $elem;

            }

            function getHeader($table) {

                return buildRows($table.find('thead tr'), 'th', 'tf-header', 'tf-row', 'tf-cell');
            };

            function getFooter($table) {

                return buildRows($table.find('tfoot tr'), 'td', 'tf-footer', 'tf-row', 'tf-cell');
            };

            function getColumns($table){

                var $elem = $('<div>').addClass('tf-columns-left');

                $header = buildColumns($table.find('thead tr'), 'th', 'tf-thead', 'tf-row', 'tf-cell');
                $body = buildColumns($table.find('tbody tr'), 'td', 'tf-tbody', 'tf-row', 'tf-cell');
                $footer = buildColumns($table.find('tfoot tr'), 'td', 'tf-tfoot', 'tf-row', 'tf-cell');

                $header.css('height', 'auto');
                $footer.css('height', 'auto');

                $elem.append($header).append($body).append($footer);

                return $elem;
            }

            function init($table) {

                var $tableContainer = $table.parent(),
                    tableContainerW = $tableContainer.width();
                tableW = $table.outerWidth(),
                    $overlayTable = $table.clone(true),
                    $overlayTableHeader = getHeader($table),
                    $overlayTableFooter = getFooter($table),
                    $overlayTableColumns = getColumns($table),
                    $viewport = $('<div id="tf-viewport">');

                $(cfg.parent).css('position', 'relative');

                $tableContainer.css('position', 'relative');
                $overlayTableHeader.css('width', (tableW + 5) + 'px')
                    .css('background', 'white')
                    .css('position', 'absolute')
                    .css('top', 0)
                    .css('left', 0);

                $overlayTableFooter.css('width', (tableW + 5) + 'px')
                    .css('background', 'white')
                    .css('position', 'absolute')
                    .css('bottom', 0)
                    .css('left', 0);

                $overlayTableColumns.css('width','auto')
                    .css('background', 'white')
                    .css('position', 'absolute')
                    .css('top', 0)
                    .css('left', 0);

                $tableContainer.append($overlayTableHeader);
                $tableContainer.append($overlayTableFooter);
                $(cfg.parent).append($overlayTableColumns);

                $viewport.css('width', tableW + 'px')
                    .css('height', '400px')
                    .css('overflow-y', 'auto')
                    .css('overflow-x', 'visible')
                    .css('position', 'relative');

                $overlayTableColumns.find('.tf-tbody').css('height', (400 - $overlayTableHeader.outerHeight() - $overlayTableFooter.outerHeight()) + 'px')
                    .css('overflow-y', 'auto')
                    .css('overflow-x', 'visible');

                $viewport.on('scroll', function () {
                    $overlayTableColumns.find('.tf-tbody').scrollTop($(this).scrollTop());
                });

                $overlayTableColumns.find('.tf-tbody').on('scroll', function () {
                    $viewport.scrollTop($(this).scrollTop());
                });

                $table.wrapAll($viewport);
            }

            return this;
        }
    });
})(jQuery);
