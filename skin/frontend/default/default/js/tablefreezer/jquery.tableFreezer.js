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
            var that = this,
                scrollXFactor,
                scrollYFactor,
                scrollErrorFactor = 0.00645; //Fix for scrolling rows not equal

            var cfg = $.extend(true, {
                parent: '#container',
                freezeHeader: true,
                freezeFooter: false,
                freezeColumnsLeft: false,  // provide integer as a number of columns you want to freeze on the left hand side
                freezeColumnsRight: false,  // provide integer as a number of columns you want to freeze on the right hand side
                lockScroller: false,
                recalculateScrollbarsOn: false,
                syncEvents: false,
                css:{
                    cellBorder: '1px solid #CCC',
                    footerBgColor: "#EEE"
                }
            }, options);

            this.each(function() {

                var $table = $(this);

                //check if table has any rows
                if($table.find('.empty-text').length){
                    return false;
                }

                if(cfg.lockScroller){

                    // Turn off main scrollbars
                    $("body").css("overflow", "hidden");

                }

                imagesLoaded( cfg.parent, function() {
                    init($table);
                });
            });

            this.recalculateWidths = function(){
                var tableW = $table.outerWidth();

                $("#tf-viewport").css('width', tableW + 'px');
                $(".tf-header").css('width', (tableW + 5) + 'px');


            };

            this.recalculateColumnsWidth = function(){

                var rowWidth;

                // Recalculate header
                if(cfg.freezeHeader){
                    $table.find('thead th').each(function(i){
                        rowWidth = $(this).outerWidth();
                        $(".tf-header .tf-cell").eq(i).css('width', rowWidth + "px");
                    });
                }

                // Recalculate left columns header
                if(cfg.freezeColumnsLeft){
                    $table.find('thead th').each(function(i){

                        if(i == cfg.freezeColumnsLeft){
                            return false;
                        }

                        rowWidth = $(this).outerWidth();
                        $(".tf-columns-left .tf-thead .tf-cell").eq(i).css('width', rowWidth + "px");
                    });
                }

            };

            this.recalculateScrollBars = function(){

                var $scrollBarContainerX = $("#scrollbar-container-x"),
                    $scrollBarContainerY = $('#scrollbar-container-y'),
                    $scrollHandleX = $("#scroll-handle-x"),
                    $scrollHandleY = $("#scroll-handle-y"),
                    scrollHandleWidth,
                    scrollHandleHeight;

                scrollHandleWidth = ( $(cfg.parent).outerWidth() * $scrollBarContainerX.width()) / $table.outerWidth()
                $scrollHandleX.width(scrollHandleWidth);
                scrollXFactor = ($table.outerWidth())  / $scrollBarContainerX.outerWidth();

                scrollHandleHeight = ( $(cfg.parent).outerHeight() * $scrollBarContainerY.height()) / $table.outerHeight()
                $scrollHandleY.height(scrollHandleHeight);
                scrollYFactor = ($table.outerHeight())  / $scrollBarContainerY.outerHeight();
            }

            this.appendRowsToLeftColumns = function(currentRows){

                if(cfg.freezeColumnsLeft){

//                    imagesLoaded( cfg.parent, function() {
//                    });
                        $body = buildColumns($table.find('tr:gt(' + (currentRows + 1) + ')'), 'td', 'tf-tbody', 'tf-row', 'tf-cell');

                }
                $('.tf-columns-left .tf-tbody').append($body.find('.tf-row'));
            }

            function buildRows($sourceRow, sourceCellElem, elemClass, rowClass, cellClass){

                var $elem = $('<div>').addClass(elemClass),
                    totalRows = $sourceRow.length,
                    totalCols;

//                    if(cfg.css.footerBgColor){
//                        $footer.css('background', cfg.css.footerBgColor);
//                    }

                $sourceRow.each(function(i){

                    $orgRow = $(this);
                    $row = $('<div>').addClass(rowClass).addClass('clearfix');

                    $elem.append($row);

//                    $elem.css('border', cfg.css.cellBorder);

                    totalCols = $orgRow.find(sourceCellElem).length;

                    $orgRow.find(sourceCellElem).each(function(j){

                        $orgCell = $(this);

                        var w = $orgCell.outerWidth();
                        var h = $orgCell.outerHeight();
                        var paddingLeft = $orgCell.css('padding-left');
                        var paddingRight = $orgCell.css('padding-right');
                        var paddingTop = $orgCell.css('padding-top');
                        var paddingBottom = $orgCell.css('padding-bottom');
                        var html = $orgCell.html();
                        var $cell = $('<div>').addClass(cellClass).html(html);

                        $cell.css('width', (w) + 'px')
                            .css('height', (h) + 'px')
                            .css('padding-left', paddingLeft)
                            .css('padding-right', paddingRight)
                            .css('padding-top', paddingTop)
                            .css('padding-bottom', paddingBottom)
                            .css('float', 'left');

                        if(i < totalRows - 1){
                            $cell.css('border-bottom', cfg.css.cellBorder);
                        }

                        if(j < totalCols - 1){
                            $cell.css('border-right', cfg.css.cellBorder);
                        }

                        $row.append($cell);
                    });


                });
                return $elem;

            }

            function buildColumns($sourceRow, sourceCellElem, elemClass, rowClass, cellClass){

                var $elem = $('<div>').addClass(elemClass),
                    totalRows = $sourceRow.length,
                    totalCols;

//                    if(cfg.css.footerBgColor){
//                        $footer.css('background', cfg.css.footerBgColor);
//                    }

                $sourceRow.each(function(i){

                    $orgRow = $(this);
                    $row = $('<div>').addClass(rowClass).addClass('clearfix');

                    $elem.append($row);

//                    $elem.css('border', cfg.css.cellBorder);

                    totalCols = $orgRow.find(sourceCellElem).length;

                    $orgRow.find(sourceCellElem).each(function(j){

                        if(j === cfg.freezeColumnsLeft){
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

                        $cell.css('width', (w) + 'px')
                            .css('height', (h) + 'px')
                            .css('padding-left', paddingLeft)
                            .css('padding-right', paddingRight)
                            .css('padding-top', paddingTop)
                            .css('padding-bottom', paddingBottom)
                            .css('float', 'left');

                        if(i < totalRows - 1){
                            $cell.css('border-bottom', cfg.css.cellBorder);
                        }

                        if(j < totalCols - 1){
                            $cell.css('border-right', cfg.css.cellBorder);
                        }

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

                $header.css('height', 'auto')
                       .css('border-bottom', cfg.css.cellBorder);

                $elem.append($header).append($body);

                if(cfg.freezeFooter){
                    $footer = buildColumns($table.find('tfoot tr'), 'td', 'tf-tfoot', 'tf-row', 'tf-cell');
                    $footer.css('height', 'auto');
                    $elem.append($footer);
                }

                $elem.css('border-top', cfg.css.cellBorder)
                     .css('border-left', cfg.css.cellBorder )

                return $elem;
            }

            function positionScrollbars($scrollX, $scrollY){

                var $scrollX = $scrollX || $("#scrollbar-container-x"),
                    $scrollY = $scrollY || $("#scrollbar-container-y"),
                    leftW = $(".tf-columns-left").outerWidth(),
                    headerH = ($('.tf-header').length) ? $('.tf-header').outerHeight() : 0,
                    footerH = ($('.tf-footer').length) ? $('.tf-footer').outerHeight() : 0;

                $scrollX.css('width', ($(cfg.parent).outerWidth() - leftW) + 'px')
                        .css('left', leftW + 'px');

                $scrollY.css('height', ($(cfg.parent).outerHeight() - headerH - footerH) + 'px')
                        .css('top', headerH + 'px');

            }

            function getAbsHeight($elem) {
                var windowH = $(window).height(),
                    heightBottomPadding = 30,
                    viewportH;

                return windowH - $elem.offset().top - heightBottomPadding;
            }

            function setTableHeight(){

                viewportH = getAbsHeight($("#tf-viewport"));

                $("#tf-viewport").css("height", viewportH + "px");

                $overlayTableColumns.find('.tf-tbody').css('height', (viewportH - $overlayTableHeader.outerHeight() - $overlayTableFooter.outerHeight()) + 'px')
                    .css('overflow-y', 'hidden')
                    .css('overflow-x', 'visible');
            }

            function scrollTableY(speed, scrollYFactor, direction){

                var $scrollHandleY = $(cfg.parent).find('#scroll-handle-y');
                var $scrollbarContainerY = $scrollHandleY.parent();

                if(!$scrollbarContainerY.length){
                    return false;
                }

                var currHandleTop = $scrollHandleY.position().top;
                var newHandleTop = currHandleTop + speed;

                if (direction == 'down') {
                    newHandleTop = currHandleTop + speed;
                } else {
                    newHandleTop = currHandleTop - speed;
                }

                if(newHandleTop < 0 && direction == 'up'){
                    $scrollHandleY.css('top', 0 + 'px');
                    $('#tf-viewport').scrollTop(0);
                    $('.tf-tbody').scrollTop(0);
                    return false;
                }
                else if(newHandleTop >= ($scrollbarContainerY.height() - $scrollHandleY.height()) && direction == 'down'){
                    $scrollHandleY.css('top', ($scrollbarContainerY.height() - $scrollHandleY.height()) + 'px');
                    $('#tf-viewport').scrollTop($('#tf-viewport')[0].scrollHeight);
                    $('.tf-tbody').scrollTop($('#tf-viewport')[0].scrollHeight);
                    return false;
                }

                $scrollHandleY.css('top', (newHandleTop) + 'px');
                $('#tf-viewport').scrollTop((newHandleTop * scrollYFactor));
                $('.tf-tbody').scrollTop((newHandleTop * scrollYFactor - (newHandleTop * scrollYFactor * scrollErrorFactor)));

//                $('#tf-viewport').scrollTop((newHandleTop * scrollYFactor));
//                $('.tf-tbody').scrollTop((newHandleTop * scrollYFactor));

                return newHandleTop;
            }

            function syncFilterEvents($overlayElem, $orgElem){

                var value = $overlayElem.val();

                if($orgElem.length){
                    $orgElem.val(value);

                    $orgElem.focus();
                    $overlayElem.focus();
                }
            }

            function init($table) {

                var $tableContainer = $table.parent(),
                    tableW = $table.outerWidth(),
                    $overlayTableHeader = getHeader($table),
                    $overlayTableFooter = getFooter($table),
                    $overlayTableColumns = getColumns($table),
                    $viewport = $('<div id="tf-viewport">'),
                    $scrollbarContainerX = $('<div id="scrollbar-container-x"></div>'),
                    $scrollbarContainerY = $('<div id="scrollbar-container-y"></div>'),
                    $scrollHandleX = $('<div id="scroll-handle-x"></div>'),
                    $scrollHandleY = $('<div id="scroll-handle-y"></div>'),
                    $loader = $('<div id="loader"></div>'),
                    scrollHandleWidth,
                    scrollHandleHeight,
                    viewportH;

                $(cfg.parent).css('position', 'relative')
                             .css('border-right', cfg.css.cellBorder)
                             .css('border-bottom', cfg.css.cellBorder);

                $tableContainer.css('position', 'relative').css('margin', '0');
                $overlayTableHeader.css('width', (tableW + 5) + 'px')
                    .css('background', 'white')
                    .css('border', cfg.css.cellBorder)
                    .css('position', 'absolute')
                    .css('top', 0)
                    .css('left', 0);

                // Hide filters
                $("#attr-filters").remove();

                // Add loader
                $(cfg.parent).append($loader);

                if(cfg.freezeFooter){
                    $overlayTableFooter.css('width', (tableW + 5) + 'px')
                        .css('background', 'white')
                        .css('position', 'absolute')
                        .css('bottom', 0)
                        .css('left', 0);

                    $tableContainer.append($overlayTableFooter);
                }

                $overlayTableColumns.css('width','auto')
                    .css('background', 'white')
                    .css('position', 'absolute')
                    .css('top', 0)
                    .css('left', 0);

                $tableContainer.append($overlayTableHeader);
                $(cfg.parent).append($overlayTableColumns);

                $viewport.css('width', tableW + 'px')
                    .css('overflow-y', 'hidden')
                    .css('overflow-x', 'visible')
                    .css('position', 'relative');

                $table.wrapAll($viewport);

                // Set height
                viewportH = getAbsHeight($("#tf-viewport"));

                if(viewportH < $table.outerHeight()){
                    $("#tf-viewport").css("height", viewportH + "px");
                    $overlayTableColumns.find('.tf-tbody').css('height', (viewportH - $overlayTableHeader.outerHeight() - $overlayTableFooter.outerHeight()) + 'px')
                        .css('overflow-y', 'hidden')
                        .css('overflow-x', 'hidden');
                }

                // Scroll bars
                if($(cfg.parent).outerHeight() < $table.outerHeight()){
                    $scrollbarContainerY.append($scrollHandleY);
                    $(cfg.parent).append($scrollbarContainerY);
                }

                if($(cfg.parent).outerWidth() < $table.outerWidth()){
                    $scrollbarContainerX.append($scrollHandleX);
                    $(cfg.parent).append($scrollbarContainerX);
                }

                positionScrollbars($scrollbarContainerX, $scrollbarContainerY);

                scrollHandleWidth = ( $(cfg.parent).outerWidth() * $scrollbarContainerX.width()) / $table.outerWidth()
                $scrollHandleX.width(scrollHandleWidth);
                scrollXFactor = ($table.outerWidth())  / $scrollbarContainerX.outerWidth();

                scrollHandleHeight = ( $(cfg.parent).outerHeight() * $scrollbarContainerY.height()) / $table.outerHeight()
                $scrollHandleY.height(scrollHandleHeight);
                scrollYFactor = ($table.outerHeight())  / $scrollbarContainerY.outerHeight();

                $scrollHandleX.draggable({
                    axis: "x",
                    containment: "parent",
                    start: function(){
                        $(this).parent().addClass('dragging');
                        $scrollbarContainerY.hide();
                    },
                    drag: function(){
                        var posLeft = $(this).position().left;
                        $(cfg.parent).find('.hor-scroll').scrollLeft(posLeft * scrollXFactor);

                    },
                    stop: function(){
                        $(this).parent().removeClass('dragging');
                        $scrollbarContainerY.show();
                    }
                });

                $scrollHandleY.draggable({
                    axis: "y",
                    containment: "parent",
                    start: function(){
                        $(this).parent().addClass('dragging');
                        $scrollbarContainerX.hide();
                    },
                    drag: function(){
                        var posTop = $(this).position().top;
                        $('#tf-viewport').scrollTop(posTop * scrollYFactor);
                        $('.tf-tbody').scrollTop((posTop * scrollYFactor - (posTop * scrollYFactor * scrollErrorFactor)));
                    },
                    stop: function(){
                        $(this).parent().removeClass('dragging');
                        $scrollbarContainerX.show();
                    }
                });

                // Table sorting
                $('.tf-header .nobr a, .tf-thead .nobr a').on('click', function(e){

                    var name = $(this).attr('name');
                    $table.find('.nobr a[name="' + name + '"]')[0].click();

                    return false;
                });

                // Mousewheel
                $(cfg.parent).on('mousewheel', function(event) {

                    var speed = $("#scroll-handle-y").height() / 5,
                        direction = (event.deltaY > 0) ? 'up' : 'down';

                    scrollTableY(speed, scrollYFactor, direction);

                });

                // Key press
                $(document).keydown(function(e){

                    var speed = $("#scroll-handle-y").height() / 5;

                    // Move up
                    if (e.keyCode == 38) {

                        scrollTableY(speed, scrollYFactor, 'up');

                        return false;
                    }
                    // Move down
                    else if(e.keyCode == 40){

                        scrollTableY(speed, scrollYFactor, 'down');

                        return false;
                    }

                });

                // Window resize
                $(window).on('resize', $.throttle( 250, function(){

                    // Set height
                    viewportH = getAbsHeight($("#tf-viewport"));

                    if(viewportH < $table.outerHeight()){
                        $("#tf-viewport").css("height", viewportH + "px");
                        $overlayTableColumns.find('.tf-tbody').css('height', (viewportH - $overlayTableHeader.outerHeight() - $overlayTableFooter.outerHeight()) + 'px');
                    }

                    // Set scrollbars
                    positionScrollbars($scrollbarContainerX, $scrollbarContainerY);

                    scrollHandleWidth = ( $(cfg.parent).outerWidth() * $scrollbarContainerX.width()) / $table.outerWidth()
                    $scrollHandleX.width(scrollHandleWidth);
                    scrollXFactor = ($table.outerWidth())  / $scrollbarContainerX.outerWidth();

                    scrollHandleHeight = ( $(cfg.parent).outerHeight() * $scrollbarContainerY.height()) / $table.outerHeight()
                    $scrollHandleY.height(scrollHandleHeight);
                    scrollYFactor = ($table.outerHeight())  / $scrollbarContainerY.outerHeight();
                } ));


                // Recalculate scrollbars event
                if(cfg.recalculateScrollbarsOn){

                    for(var i=0; i<cfg.recalculateScrollbarsOn.length; i++){

                        var obj = cfg.recalculateScrollbarsOn[i];
                        $(obj.targetElem).on(obj.event, function(){

                            //TODO: REFACTOR
                            positionScrollbars($scrollbarContainerX, $scrollbarContainerY);

                            scrollHandleWidth = ( $(cfg.parent).outerWidth() * $scrollbarContainerX.width()) / $table.outerWidth()
                            $scrollHandleX.width(scrollHandleWidth);
                            scrollXFactor = ($table.outerWidth())  / $scrollbarContainerX.outerWidth();

                            scrollHandleHeight = ( $(cfg.parent).outerHeight() * $scrollbarContainerY.height()) / $table.outerHeight()
                            $scrollHandleY.height(scrollHandleHeight);
                            scrollYFactor = ($table.outerHeight())  / $scrollbarContainerY.outerHeight();
                        });

                    }
                }

                if(cfg.syncEvents){

                    for(var i=0; i<cfg.syncEvents.length; i++){

                        var obj = cfg.syncEvents[i];

                        if(obj.many){
                            $(obj.sourceElem).each(function(){

                                $('body').on(obj.sourceEvent, $(this), function(){
                                    if(obj.callback) obj.callback.apply(null, [$(this)]);
                                    if(obj.tfEvent) obj.tfEvent.apply(null, [$(this)]);
                                });

                            });
                        }
                        else{
                            $('body').on(obj.sourceEvent, obj.sourceElem, function(){
                                if(obj.callback) obj.callback.apply(null, [$(this)]);
                                if(obj.tfEvent) obj.tfEvent.apply(null, [$(this)]);
                            });
                        }
                    }
                }
            }

            return this;
        }
    });
})(jQuery);
