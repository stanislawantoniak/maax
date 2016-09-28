/*JS Lint helpers: */
/*global dragMove: false, dragEnd: false, $, jQuery, alert, window, document */
/*jslint nomen: true, continue:true */

if (typeof Object.create !== "function") {
    Object.create = function (obj) {
        function F() {}
        F.prototype = obj;
        return new F();
    };
}
(function ($, window, document) {

    var Carousel = {
        init : function (options, el) {
            var primal = this;

            primal.$elem = $(el);
            primal.options = $.extend({}, $.fn.rwdCarousel.options, primal.$elem.data(), options);

            primal.userOptions = options;
            primal.loadContent();
        },

        loadContent : function () {
            var primal = this, url;

            function getData(data) {
                var i, content = "";
                if (typeof primal.options.jsonSuccess === "function") {
                    primal.options.jsonSuccess.apply(this, [data]);
                } else {
                    for (i in data.rwd) {
                        if (data.rwd.hasOwnProperty(i)) {
                            content += data.rwd[i].item;
                        }
                    }
                    primal.$elem.html(content);
                }
                primal.logIn();
            }

            if (typeof primal.options.beforeInit === "function") {
                primal.options.beforeInit.apply(this, [primal.$elem]);
            }

            if (typeof primal.options.jsonPath === "string") {
                url = primal.options.jsonPath;
                $.getJSON(url, getData);
            } else {
                primal.logIn();
            }
        },

        logIn : function () {
            var primal = this;

            primal.$elem.data("rwd-originalStyles", primal.$elem.attr("style"));
            primal.$elem.data("rwd-originalClasses", primal.$elem.attr("class"));

            primal.$elem.css({opacity: 0});
            primal.orignalItems = primal.options.items;
            primal.checkBrowser();
            primal.wrapperWidth = 0;
            primal.checkVisible = null;
            primal.setVars();
        },

        setVars : function () {
            var primal = this;
            if (primal.$elem.children().length === 0) {return false; }
            primal.primalClass();
            primal.eventTypes();
            primal.$userItems = primal.$elem.children();
            primal.itemsAmount = primal.$userItems.length;
            primal.wrapItems();
            primal.$rwdItems = primal.$elem.find(".rwd-item");
            primal.$rwdWrapper = primal.$elem.find(".rwd-wrapper");
            primal.playDirection = "next";
            primal.prevItem = 0;
            primal.prevArr = [0];
            primal.currentItem = 0;
            primal.customEvents();
            primal.onStartup();
        },

        onStartup : function () {
            var primal = this;
            primal.updateItems();
            primal.calculateAll();
            primal.buildControls();
            primal.updateControls();
            primal.response();
            primal.moveEvents();
            primal.stopOnHover();
            primal.rwdStatus();

            if (primal.options.transitionStyle !== false) {
                primal.transitionTypes(primal.options.transitionStyle);
            }
            if (primal.options.autoPlay === true) {
                primal.options.autoPlay = 5000;
            }
            primal.play();

            primal.$elem.find(".rwd-wrapper").css("display", "block");

            if (!primal.$elem.is(":visible")) {
                primal.watchVisibility();
            } else {
                primal.$elem.css("opacity", 1);
            }
            primal.onstartup = false;
            primal.eachMoveUpdate();
            if (typeof primal.options.afterInit === "function") {
                primal.options.afterInit.apply(this, [primal.$elem]);
            }
        },

        eachMoveUpdate : function () {
            var primal = this;

            if (primal.options.lazyLoad === true) {
                primal.lazyLoad();
            }
            if (primal.options.autoHeight === true) {
                primal.autoHeight();
            }
            primal.onVisibleItems();

            if (typeof primal.options.afterAction === "function") {
                primal.options.afterAction.apply(this, [primal.$elem]);
            }
        },

        updateVars : function () {
            var primal = this;
            if (typeof primal.options.beforeUpdate === "function") {
                primal.options.beforeUpdate.apply(this, [primal.$elem]);
            }
            primal.watchVisibility();
            primal.updateItems();
            primal.calculateAll();
            primal.updatePosition();
            primal.updateControls();
            primal.eachMoveUpdate();
            if (typeof primal.options.afterUpdate === "function") {
                primal.options.afterUpdate.apply(this, [primal.$elem]);
            }
        },

        reload : function () {
            var primal = this;
            window.setTimeout(function () {
                primal.updateVars();
            }, 0);
        },

        watchVisibility : function () {
            var primal = this;

            if (primal.$elem.is(":visible") === false) {
                primal.$elem.css({opacity: 0});
                window.clearInterval(primal.autoPlayInterval);
                window.clearInterval(primal.checkVisible);
            } else {
                return false;
            }
            primal.checkVisible = window.setInterval(function () {
                if (primal.$elem.is(":visible")) {
                    primal.reload();
                    primal.$elem.animate({opacity: 1}, 200);
                    window.clearInterval(primal.checkVisible);
                }
            }, 500);
        },

        wrapItems : function () {
            var primal = this;
            primal.$userItems.wrapAll("<div class=\"rwd-wrapper\">").wrap("<div class=\"rwd-item\"></div>");
            primal.$elem.find(".rwd-wrapper").wrap("<div class=\"rwd-wrapper-outer\">");
            primal.wrapperOuter = primal.$elem.find(".rwd-wrapper-outer");
            primal.$elem.css("display", "block");
        },

        primalClass : function () {
            var primal = this,
                hasprimalClass = primal.$elem.hasClass(primal.options.primalClass),
                hasThemeClass = primal.$elem.hasClass(primal.options.theme);

            if (!hasprimalClass) {
                primal.$elem.addClass(primal.options.primalClass);
            }

            if (!hasThemeClass) {
                primal.$elem.addClass(primal.options.theme);
            }
        },

        updateItems : function () {
            var primal = this, width, i;

            if (primal.options.responsive === false) {
                return false;
            }
            if (primal.options.singleItem === true) {
                primal.options.items = primal.orignalItems = 1;
                primal.options.itemsCustom = false;
                primal.options.itemsDesktop = false;
                primal.options.itemsDesktopSmall = false;
                primal.options.itemsTablet = false;
                primal.options.itemsTabletSmall = false;
                primal.options.itemsMobile = false;
                return false;
            }

            width = $(primal.options.responsiveprimalWidth).width();

            if (width > (primal.options.itemsDesktop[0] || primal.orignalItems)) {
                primal.options.items = primal.orignalItems;
            }
            if (primal.options.itemsCustom !== false) {
                //Reorder array by screen size
                primal.options.itemsCustom.sort(function (a, b) {return a[0] - b[0]; });

                for (i = 0; i < primal.options.itemsCustom.length; i += 1) {
                    if (primal.options.itemsCustom[i][0] <= width) {
                        primal.options.items = primal.options.itemsCustom[i][1];
                    }
                }

            } else {

                if (width <= primal.options.itemsDesktop[0] && primal.options.itemsDesktop !== false) {
                    primal.options.items = primal.options.itemsDesktop[1];
                }

                if (width <= primal.options.itemsDesktopSmall[0] && primal.options.itemsDesktopSmall !== false) {
                    primal.options.items = primal.options.itemsDesktopSmall[1];
                }

                if (width <= primal.options.itemsTablet[0] && primal.options.itemsTablet !== false) {
                    primal.options.items = primal.options.itemsTablet[1];
                }

                if (width <= primal.options.itemsTabletSmall[0] && primal.options.itemsTabletSmall !== false) {
                    primal.options.items = primal.options.itemsTabletSmall[1];
                }

                if (width <= primal.options.itemsMobile[0] && primal.options.itemsMobile !== false) {
                    primal.options.items = primal.options.itemsMobile[1];
                }
            }

            //if number of items is less than declared
            if (primal.options.items > primal.itemsAmount && primal.options.itemsScaleUp === true) {
                primal.options.items = primal.itemsAmount;
            }
        },

        response : function () {
            var primal = this,
                smallDelay,
                lastWindowWidth;

            if (primal.options.responsive !== true) {
                return false;
            }
            lastWindowWidth = $(window).width();

            primal.resizer = function () {
                if ($(window).width() !== lastWindowWidth) {
                    if (primal.options.autoPlay !== false) {
                        window.clearInterval(primal.autoPlayInterval);
                    }
                    window.clearTimeout(smallDelay);
                    smallDelay = window.setTimeout(function () {
                        lastWindowWidth = $(window).width();
                        primal.updateVars();
                    }, primal.options.responsiveRefreshRate);
                }
            };
            $(window).resize(primal.resizer);
        },

        updatePosition : function () {
            var primal = this;
            primal.jumpTo(primal.currentItem);
            if (primal.options.autoPlay !== false) {
                primal.checkAp();
            }
        },

        appendItemsSizes : function () {
            var primal = this,
                roundPages = 0,
                lastItem = primal.itemsAmount - primal.options.items;

            primal.$rwdItems.each(function (index) {
                var $this = $(this);
                $this
                    .css({"width": primal.itemWidth})
                    .data("rwd-item", Number(index));

                if (index % primal.options.items === 0 || index === lastItem) {
                    if (!(index > lastItem)) {
                        roundPages += 1;
                    }
                }
                $this.data("rwd-roundPages", roundPages);
            });
        },

        appendWrapperSizes : function () {
            var primal = this,
                width = primal.$rwdItems.length * primal.itemWidth;

            primal.$rwdWrapper.css({
                "width": width * 2,
                "left": 0
            });
            primal.appendItemsSizes();
        },

        calculateAll : function () {
            var primal = this;
            primal.calculateWidth();
            primal.appendWrapperSizes();
            primal.loops();
            primal.max();
        },

        calculateWidth : function () {
            var primal = this;
            primal.itemWidth = Math.round(primal.$elem.width() / primal.options.items);
        },

        max : function () {
            var primal = this,
                maximum = ((primal.itemsAmount * primal.itemWidth) - primal.options.items * primal.itemWidth) * -1;
            if (primal.options.items > primal.itemsAmount) {
                primal.maximumItem = 0;
                maximum = 0;
                primal.maximumPixels = 0;
            } else {
                primal.maximumItem = primal.itemsAmount - primal.options.items;
                primal.maximumPixels = maximum;
            }
            return maximum;
        },

        min : function () {
            return 0;
        },

        loops : function () {
            var primal = this,
                prev = 0,
                elWidth = 0,
                i,
                item,
                roundPageNum;

            primal.positionsInArray = [0];
            primal.pagesInArray = [];

            for (i = 0; i < primal.itemsAmount; i += 1) {
                elWidth += primal.itemWidth;
                primal.positionsInArray.push(-elWidth);

                if (primal.options.scrollPerPage === true) {
                    item = $(primal.$rwdItems[i]);
                    roundPageNum = item.data("rwd-roundPages");
                    if (roundPageNum !== prev) {
                        primal.pagesInArray[prev] = primal.positionsInArray[i];
                        prev = roundPageNum;
                    }
                }
            }
        },

        buildControls : function () {
            var primal = this;
            if (primal.options.navigation === true || primal.options.pagination === true) {
                primal.rwdControls = $("<div class=\"rwd-controls\"/>").toggleClass("clickable", !primal.browser.isTouch).appendTo(primal.$elem);
            }
            if (primal.options.pagination === true) {
                primal.buildPagination();
            }
            if (primal.options.navigation === true) {
                primal.buildButtons();
            }
        },

        buildButtons : function () {
            var primal = this,
                buttonsWrapper = $("<div class=\"rwd-buttons\"/>");
            primal.rwdControls.append(buttonsWrapper);

            primal.buttonPrev = $("<div/>", {
                "class" : "rwd-prev",
                "html" : primal.options.navigationText[0] || ""
            });

            primal.buttonNext = $("<div/>", {
                "class" : "rwd-next",
                "html" : primal.options.navigationText[1] || ""
            });

            buttonsWrapper
                .append(primal.buttonPrev)
                .append(primal.buttonNext);

            buttonsWrapper.on("touchstart.rwdControls mousedown.rwdControls", "div[class^=\"rwd\"]", function (event) {
                event.preventDefault();
            });

            buttonsWrapper.on("touchend.rwdControls mouseup.rwdControls", "div[class^=\"rwd\"]", function (event) {
                event.preventDefault();
                if ($(this).hasClass("rwd-next")) {
                    primal.next();
                } else {
                    primal.prev();
                }
            });
        },

        buildPagination : function () {
            var primal = this;

            primal.paginationWrapper = $("<div class=\"rwd-pagination\"/>");
            primal.rwdControls.append(primal.paginationWrapper);

            primal.paginationWrapper.on("touchend.rwdControls mouseup.rwdControls", ".rwd-page", function (event) {
                event.preventDefault();
                if (Number($(this).data("rwd-page")) !== primal.currentItem) {
                    primal.goTo(Number($(this).data("rwd-page")), true);
                }
            });
        },

        updatePagination : function () {
            var primal = this,
                counter,
                lastPage,
                lastItem,
                i,
                paginationButton,
                paginationButtonInner;

            if (primal.options.pagination === false) {
                return false;
            }

            primal.paginationWrapper.html("");

            counter = 0;
            lastPage = primal.itemsAmount - primal.itemsAmount % primal.options.items;

            for (i = 0; i < primal.itemsAmount; i += 1) {
                if (i % primal.options.items === 0) {
                    counter += 1;
                    if (lastPage === i) {
                        lastItem = primal.itemsAmount - primal.options.items;
                    }
                    paginationButton = $("<div/>", {
                        "class" : "rwd-page"
                    });
                    paginationButtonInner = $("<span></span>", {
                        "text": primal.options.paginationNumbers === true ? counter : "",
                        "class": primal.options.paginationNumbers === true ? "rwd-numbers" : ""
                    });
                    paginationButton.append(paginationButtonInner);

                    paginationButton.data("rwd-page", lastPage === i ? lastItem : i);
                    paginationButton.data("rwd-roundPages", counter);

                    primal.paginationWrapper.append(paginationButton);
                }
            }
            primal.checkPagination();
        },
        checkPagination : function () {
            var primal = this;
            if (primal.options.pagination === false) {
                return false;
            }
            primal.paginationWrapper.find(".rwd-page").each(function () {
                if ($(this).data("rwd-roundPages") === $(primal.$rwdItems[primal.currentItem]).data("rwd-roundPages")) {
                    primal.paginationWrapper
                        .find(".rwd-page")
                        .removeClass("active");
                    $(this).addClass("active");
                }
            });
        },

        checkNavigation : function () {
            var primal = this;

            if (primal.options.navigation === false) {
                return false;
            }
            if (primal.options.rewindNav === false) {
                if (primal.currentItem === 0 && primal.maximumItem === 0) {
                    primal.buttonPrev.addClass("disabled");
                    primal.buttonNext.addClass("disabled");
                } else if (primal.currentItem === 0 && primal.maximumItem !== 0) {
                    primal.buttonPrev.addClass("disabled");
                    primal.buttonNext.removeClass("disabled");
                } else if (primal.currentItem === primal.maximumItem) {
                    primal.buttonPrev.removeClass("disabled");
                    primal.buttonNext.addClass("disabled");
                } else if (primal.currentItem !== 0 && primal.currentItem !== primal.maximumItem) {
                    primal.buttonPrev.removeClass("disabled");
                    primal.buttonNext.removeClass("disabled");
                }
            }
        },

        updateControls : function () {
            var primal = this;
            primal.updatePagination();
            primal.checkNavigation();
            if (primal.rwdControls) {
                if (primal.options.items >= primal.itemsAmount) {
                    primal.rwdControls.hide();
                } else {
                    primal.rwdControls.show();
                }
            }
        },

        destroyControls : function () {
            var primal = this;
            if (primal.rwdControls) {
                primal.rwdControls.remove();
            }
        },

        next : function (speed) {
            var primal = this;

            if (primal.isTransition) {
                return false;
            }

            primal.currentItem += primal.options.scrollPerPage === true ? primal.options.items : 1;
            if (primal.currentItem > primal.maximumItem + (primal.options.scrollPerPage === true ? (primal.options.items - 1) : 0)) {
                if (primal.options.rewindNav === true) {
                    primal.currentItem = 0;
                    speed = "rewind";
                } else {
                    primal.currentItem = primal.maximumItem;
                    return false;
                }
            }
            primal.goTo(primal.currentItem, speed);
        },

        prev : function (speed) {
            var primal = this;

            if (primal.isTransition) {
                return false;
            }

            if (primal.options.scrollPerPage === true && primal.currentItem > 0 && primal.currentItem < primal.options.items) {
                primal.currentItem = 0;
            } else {
                primal.currentItem -= primal.options.scrollPerPage === true ? primal.options.items : 1;
            }
            if (primal.currentItem < 0) {
                if (primal.options.rewindNav === true) {
                    primal.currentItem = primal.maximumItem;
                    speed = "rewind";
                } else {
                    primal.currentItem = 0;
                    return false;
                }
            }
            primal.goTo(primal.currentItem, speed);
        },

        goTo : function (position, speed, drag) {
            var primal = this,
                goToPixel;
            if (primal.isTransition) {
                return false;
            }
            if (typeof primal.options.beforeMove === "function") {
                primal.options.beforeMove.apply(this, [primal.$elem]);
            }
            if (position >= primal.maximumItem) {
                position = primal.maximumItem;
            } else if (position <= 0) {
                position = 0;
            }

            primal.currentItem = primal.rwd.currentItem = position;
            if (primal.options.transitionStyle !== false && drag !== "drag" && primal.options.items === 1 && primal.browser.support3d === true) {
                primal.swapSpeed(0);
                if (primal.browser.support3d === true) {
                    primal.transition3d(primal.positionsInArray[position]);
                } else {
                    primal.css2slide(primal.positionsInArray[position], 1);
                }
                primal.afterGo();
                primal.singleItemTransition();
                return false;
            }
            goToPixel = primal.positionsInArray[position];

            if (primal.browser.support3d === true) {
                primal.isCss3Finish = false;

                if (speed === true) {
                    primal.swapSpeed("paginationSpeed");
                    window.setTimeout(function () {
                        primal.isCss3Finish = true;
                    }, primal.options.paginationSpeed);

                } else if (speed === "rewind") {
                    primal.swapSpeed(primal.options.rewindSpeed);
                    window.setTimeout(function () {
                        primal.isCss3Finish = true;
                    }, primal.options.rewindSpeed);

                } else {
                    primal.swapSpeed("slideSpeed");
                    window.setTimeout(function () {
                        primal.isCss3Finish = true;
                    }, primal.options.slideSpeed);
                }
                primal.transition3d(goToPixel);
            } else {
                if (speed === true) {
                    primal.css2slide(goToPixel, primal.options.paginationSpeed);
                } else if (speed === "rewind") {
                    primal.css2slide(goToPixel, primal.options.rewindSpeed);
                } else {
                    primal.css2slide(goToPixel, primal.options.slideSpeed);
                }
            }
            primal.afterGo();
        },

        jumpTo : function (position) {
            var primal = this;
            if (typeof primal.options.beforeMove === "function") {
                primal.options.beforeMove.apply(this, [primal.$elem]);
            }
            if (position >= primal.maximumItem || position === -1) {
                position = primal.maximumItem;
            } else if (position <= 0) {
                position = 0;
            }
            primal.swapSpeed(0);
            if (primal.browser.support3d === true) {
                primal.transition3d(primal.positionsInArray[position]);
            } else {
                primal.css2slide(primal.positionsInArray[position], 1);
            }
            primal.currentItem = primal.rwd.currentItem = position;
            primal.afterGo();
        },

        afterGo : function () {
            var primal = this;

            primal.prevArr.push(primal.currentItem);
            primal.prevItem = primal.rwd.prevItem = primal.prevArr[primal.prevArr.length - 2];
            primal.prevArr.shift(0);

            if (primal.prevItem !== primal.currentItem) {
                primal.checkPagination();
                primal.checkNavigation();
                primal.eachMoveUpdate();

                if (primal.options.autoPlay !== false) {
                    primal.checkAp();
                }
            }
            if (typeof primal.options.afterMove === "function" && primal.prevItem !== primal.currentItem) {
                primal.options.afterMove.apply(this, [primal.$elem]);
            }
        },

        stop : function () {
            var primal = this;
            primal.apStatus = "stop";
            window.clearInterval(primal.autoPlayInterval);
        },

        checkAp : function () {
            var primal = this;
            if (primal.apStatus !== "stop") {
                primal.play();
            }
        },

        play : function () {
            var primal = this;
            primal.apStatus = "play";
            if (primal.options.autoPlay === false) {
                return false;
            }
            window.clearInterval(primal.autoPlayInterval);
            primal.autoPlayInterval = window.setInterval(function () {
                primal.next(true);
            }, primal.options.autoPlay);
        },

        swapSpeed : function (action) {
            var primal = this;
            if (action === "slideSpeed") {
                primal.$rwdWrapper.css(primal.addCssSpeed(primal.options.slideSpeed));
            } else if (action === "paginationSpeed") {
                primal.$rwdWrapper.css(primal.addCssSpeed(primal.options.paginationSpeed));
            } else if (typeof action !== "string") {
                primal.$rwdWrapper.css(primal.addCssSpeed(action));
            }
        },

        addCssSpeed : function (speed) {
            return {
                "-webkit-transition": "all " + speed + "ms ease",
                "-moz-transition": "all " + speed + "ms ease",
                "-o-transition": "all " + speed + "ms ease",
                "transition": "all " + speed + "ms ease"
            };
        },

        removeTransition : function () {
            return {
                "-webkit-transition": "",
                "-moz-transition": "",
                "-o-transition": "",
                "transition": ""
            };
        },

        doTranslate : function (pixels) {
            return {
                "-webkit-transform": "translate3d(" + pixels + "px, 0px, 0px)",
                "-moz-transform": "translate3d(" + pixels + "px, 0px, 0px)",
                "-o-transform": "translate3d(" + pixels + "px, 0px, 0px)",
                "-ms-transform": "translate3d(" + pixels + "px, 0px, 0px)",
                "transform": "translate3d(" + pixels + "px, 0px,0px)"
            };
        },

        transition3d : function (value) {
            var primal = this;
            primal.$rwdWrapper.css(primal.doTranslate(value));
        },

        css2move : function (value) {
            var primal = this;
            primal.$rwdWrapper.css({"left" : value});
        },

        css2slide : function (value, speed) {
            var primal = this;

            primal.isCssFinish = false;
            primal.$rwdWrapper.stop(true, true).animate({
                "left" : value
            }, {
                duration : speed || primal.options.slideSpeed,
                complete : function () {
                    primal.isCssFinish = true;
                }
            });
        },

        checkBrowser : function () {
            var primal = this,
                translate3D = "translate3d(0px, 0px, 0px)",
                tempElem = document.createElement("div"),
                regex,
                asSupport,
                support3d,
                isTouch;

            tempElem.style.cssText = "  -moz-transform:" + translate3D +
                                  "; -ms-transform:"     + translate3D +
                                  "; -o-transform:"      + translate3D +
                                  "; -webkit-transform:" + translate3D +
                                  "; transform:"         + translate3D;
            regex = /translate3d\(0px, 0px, 0px\)/g;
            asSupport = tempElem.style.cssText.match(regex);
            //support3d = (asSupport !== null && asSupport.length === 1);
            support3d = (Modernizr.csstransforms3d);
            isTouch = "ontouchstart" in window || window.navigator.msMaxTouchPoints;

            primal.browser = {
                "support3d" : support3d,
                "isTouch" : isTouch
            };
        },

        moveEvents : function () {
            var primal = this;
            if (primal.options.mouseDrag !== false || primal.options.touchDrag !== false) {
                primal.gestures();
                primal.disabledEvents();
            }
        },

        eventTypes : function () {
            var primal = this,
                types = ["s", "e", "x"];

            primal.ev_types = {};

            if (primal.options.mouseDrag === true && primal.options.touchDrag === true) {
                types = [
                    "touchstart.rwd mousedown.rwd",
                    "touchmove.rwd mousemove.rwd",
                    "touchend.rwd touchcancel.rwd mouseup.rwd"
                ];
            } else if (primal.options.mouseDrag === false && primal.options.touchDrag === true) {
                types = [
                    "touchstart.rwd",
                    "touchmove.rwd",
                    "touchend.rwd touchcancel.rwd"
                ];
            } else if (primal.options.mouseDrag === true && primal.options.touchDrag === false) {
                types = [
                    "mousedown.rwd",
                    "mousemove.rwd",
                    "mouseup.rwd"
                ];
            }

            primal.ev_types.start = types[0];
            primal.ev_types.move = types[1];
            primal.ev_types.end = types[2];
        },

        disabledEvents :  function () {
            var primal = this;
            primal.$elem.on("dragstart.rwd", function (event) { event.preventDefault(); });
            primal.$elem.on("mousedown.disableTextSelect", function (e) {
                return $(e.target).is('input, textarea, select, option');
            });
        },

        gestures : function () {
            /*jslint unparam: true*/
            var primal = this,
                locals = {
                    offsetX : 0,
                    offsetY : 0,
                    primalElWidth : 0,
                    relativePos : 0,
                    position: null,
                    minSwipe : null,
                    maxSwipe: null,
                    sliding : null,
                    dargging: null,
                    targetElement : null
                };

            primal.isCssFinish = true;

            function getTouches(event) {
                if (event.touches !== undefined) {
                    return {
                        x : event.touches[0].pageX,
                        y : event.touches[0].pageY
                    };
                }

                if (event.touches === undefined) {
                    if (event.pageX !== undefined) {
                        return {
                            x : event.pageX,
                            y : event.pageY
                        };
                    }
                    if (event.pageX === undefined) {
                        return {
                            x : event.clientX,
                            y : event.clientY
                        };
                    }
                }
            }

            function swapEvents(type) {
                if (type === "on") {
                    $(document).on(primal.ev_types.move, dragMove);
                    $(document).on(primal.ev_types.end, dragEnd);
                } else if (type === "off") {
                    $(document).off(primal.ev_types.move);
                    $(document).off(primal.ev_types.end);
                }
            }

            function dragStart(event) {
                var ev = event.originalEvent || event || window.event,
                    position;

                if (ev.which === 3) {
                    return false;
                }
                if (primal.itemsAmount <= primal.options.items) {
                    return;
                }
                if (primal.isCssFinish === false && !primal.options.dragBeforeAnimFinish) {
                    return false;
                }
                if (primal.isCss3Finish === false && !primal.options.dragBeforeAnimFinish) {
                    return false;
                }

                if (primal.options.autoPlay !== false) {
                    window.clearInterval(primal.autoPlayInterval);
                }

                if (primal.browser.isTouch !== true && !primal.$rwdWrapper.hasClass("grabbing")) {
                    primal.$rwdWrapper.addClass("grabbing");
                }

                primal.newPosX = 0;
                primal.newRelativeX = 0;

                $(this).css(primal.removeTransition());

                position = $(this).position();
                locals.relativePos = position.left;

                locals.offsetX = getTouches(ev).x - position.left;
                locals.offsetY = getTouches(ev).y - position.top;

                swapEvents("on");

                locals.sliding = false;
                locals.targetElement = ev.target || ev.srcElement;
            }

            function dragMove(event) {
                var ev = event.originalEvent || event || window.event,
                    minSwipe,
                    maxSwipe;

                primal.newPosX = getTouches(ev).x - locals.offsetX;
                primal.newPosY = getTouches(ev).y - locals.offsetY;
                primal.newRelativeX = primal.newPosX - locals.relativePos;

                if (typeof primal.options.startDragging === "function" && locals.dragging !== true && primal.newRelativeX !== 0) {
                    locals.dragging = true;
                    primal.options.startDragging.apply(primal, [primal.$elem]);
                }

                if ((primal.newRelativeX > 8 || primal.newRelativeX < -8) && (primal.browser.isTouch === true)) {
                    if (ev.preventDefault !== undefined) {
                        ev.preventDefault();
                    } else {
                        ev.returnValue = false;
                    }
                    locals.sliding = true;
                }

                if ((primal.newPosY > 10 || primal.newPosY < -10) && locals.sliding === false) {
                    $(document).off("touchmove.rwd");
                }

                minSwipe = function () {
                    return primal.newRelativeX / 5;
                };

                maxSwipe = function () {
                    return primal.maximumPixels + primal.newRelativeX / 5;
                };

                primal.newPosX = Math.max(Math.min(primal.newPosX, minSwipe()), maxSwipe());
                if (primal.browser.support3d === true) {
                    primal.transition3d(primal.newPosX);
                } else {
                    primal.css2move(primal.newPosX);
                }
            }

            function dragEnd(event) {
                var ev = event.originalEvent || event || window.event,
                    newPosition,
                    handlers,
                    rwdStopEvent;

                ev.target = ev.target || ev.srcElement;

                locals.dragging = false;

                if (primal.browser.isTouch !== true) {
                    primal.$rwdWrapper.removeClass("grabbing");
                }

                if (primal.newRelativeX < 0) {
                    primal.dragDirection = primal.rwd.dragDirection = "left";
                } else {
                    primal.dragDirection = primal.rwd.dragDirection = "right";
                }

                if (primal.newRelativeX !== 0) {
                    newPosition = primal.getNewPosition();
                    primal.goTo(newPosition, false, "drag");
                    if (locals.targetElement === ev.target && primal.browser.isTouch !== true) {
                        $(ev.target).on("click.disable", function (ev) {
                            ev.stopImmediatePropagation();
                            ev.stopPropagation();
                            ev.preventDefault();
                            $(ev.target).off("click.disable");
                        });
                        handlers = $._data(ev.target, "events").click;
                        rwdStopEvent = handlers.pop();
                        handlers.splice(0, 0, rwdStopEvent);
                    }
                }
                swapEvents("off");
            }
            primal.$elem.on(primal.ev_types.start, ".rwd-wrapper", dragStart);
        },

        getNewPosition : function () {
            var primal = this,
                newPosition = primal.closestItem();

            if (newPosition > primal.maximumItem) {
                primal.currentItem = primal.maximumItem;
                newPosition  = primal.maximumItem;
            } else if (primal.newPosX >= 0) {
                newPosition = 0;
                primal.currentItem = 0;
            }
            return newPosition;
        },
        closestItem : function () {
            var primal = this,
                array = primal.options.scrollPerPage === true ? primal.pagesInArray : primal.positionsInArray,
                goal = primal.newPosX,
                closest = null;

            $.each(array, function (i, v) {
                if (goal - (primal.itemWidth / 20) > array[i + 1] && goal - (primal.itemWidth / 20) < v && primal.moveDirection() === "left") {
                    closest = v;
                    if (primal.options.scrollPerPage === true) {
                        primal.currentItem = $.inArray(closest, primal.positionsInArray);
                    } else {
                        primal.currentItem = i;
                    }
                } else if (goal + (primal.itemWidth / 20) < v && goal + (primal.itemWidth / 20) > (array[i + 1] || array[i] - primal.itemWidth) && primal.moveDirection() === "right") {
                    if (primal.options.scrollPerPage === true) {
                        closest = array[i + 1] || array[array.length - 1];
                        primal.currentItem = $.inArray(closest, primal.positionsInArray);
                    } else {
                        closest = array[i + 1];
                        primal.currentItem = i + 1;
                    }
                }
            });
            return primal.currentItem;
        },

        moveDirection : function () {
            var primal = this,
                direction;
            if (primal.newRelativeX < 0) {
                direction = "right";
                primal.playDirection = "next";
            } else {
                direction = "left";
                primal.playDirection = "prev";
            }
            return direction;
        },

        customEvents : function () {
            /*jslint unparam: true*/
            var primal = this;
            primal.$elem.on("rwd.next", function () {
                primal.next();
            });
            primal.$elem.on("rwd.prev", function () {
                primal.prev();
            });
////////////////////////////////////////////////////////////////////////////////////
            primal.$elem.on("rwd.up", function () {
                primal.next();
            });
            primal.$elem.on("rwd.down", function () {
                primal.prev();
            });
///////////////////////////////////////////////////////////////////////////////////
            primal.$elem.on("rwd.play", function (event, speed) {
                primal.options.autoPlay = speed;
                primal.play();
                primal.hoverStatus = "play";
            });
            primal.$elem.on("rwd.stop", function () {
                primal.stop();
                primal.hoverStatus = "stop";
            });
            primal.$elem.on("rwd.goTo", function (event, item) {
                primal.goTo(item);
            });
            primal.$elem.on("rwd.jumpTo", function (event, item) {
                primal.jumpTo(item);
            });
        },

        stopOnHover : function () {
            var primal = this;
            if (primal.options.stopOnHover === true && primal.browser.isTouch !== true && primal.options.autoPlay !== false) {
                primal.$elem.on("mouseover", function () {
                    primal.stop();
                });
                primal.$elem.on("mouseout", function () {
                    if (primal.hoverStatus !== "stop") {
                        primal.play();
                    }
                });
            }
        },

        lazyLoad : function () {
            var primal = this,
                i,
                $item,
                itemNumber,
                $lazyImg,
                follow;

            if (primal.options.lazyLoad === false) {
                return false;
            }
            for (i = 0; i < primal.itemsAmount; i += 1) {
                $item = $(primal.$rwdItems[i]);

                if ($item.data("rwd-loaded") === "loaded") {
                    continue;
                }

                itemNumber = $item.data("rwd-item");
                $lazyImg = $item.find(".lazyrwd");

                if (typeof $lazyImg.data("src") !== "string") {
                    $item.data("rwd-loaded", "loaded");
                    continue;
                }
                if ($item.data("rwd-loaded") === undefined) {
                    $lazyImg.hide();
                    $item.addClass("loading").data("rwd-loaded", "checked");
                }
                if (primal.options.lazyFollow === true) {
                    follow = itemNumber >= primal.currentItem;
                } else {
                    follow = true;
                }
                if (follow && itemNumber < primal.currentItem + primal.options.items && $lazyImg.length) {
                    primal.lazyPreload($item, $lazyImg);
                }
            }
        },

        lazyPreload : function ($item, $lazyImg) {
            var primal = this,
                iterations = 0,
                isBackgroundImg;

            if ($lazyImg.prop("tagName") === "DIV") {
                $lazyImg.css("background-image", "url(" + $lazyImg.data("src") + ")");
                isBackgroundImg = true;
            } else {
                $lazyImg[0].src = $lazyImg.data("src");
            }

            function showImage() {
                $item.data("rwd-loaded", "loaded").removeClass("loading");
                $lazyImg.removeAttr("data-src");
                if (primal.options.lazyEffect === "fade") {
                    $lazyImg.fadeIn(10);
                } else {
                    $lazyImg.show();
                }
                if (typeof primal.options.afterLazyLoad === "function") {
                    primal.options.afterLazyLoad.apply(this, [primal.$elem]);
                }
            }

            function checkLazyImage() {
                iterations += 1;
                if (primal.completeImg($lazyImg.get(0)) || isBackgroundImg === true) {
                    showImage();
                } else if (iterations <= 100) {//if image loads in less than 10 seconds 
                    window.setTimeout(checkLazyImage, 100);
                } else {
                    showImage();
                }
            }

            checkLazyImage();
        },

        autoHeight : function () {
            var primal = this,
                $currentimg = $(primal.$rwdItems[primal.currentItem]).find("img"),
                iterations;

            function addHeight() {
                var $currentItem = $(primal.$rwdItems[primal.currentItem]).height();
                primal.wrapperOuter.css("height", $currentItem + "px");
                if (!primal.wrapperOuter.hasClass("autoHeight")) {
                    window.setTimeout(function () {
                        primal.wrapperOuter.addClass("autoHeight");
                    }, 0);
                }
            }

            function checkImage() {
                iterations += 1;
                if (primal.completeImg($currentimg.get(0))) {
                    addHeight();
                } else if (iterations <= 100) { //if image loads in less than 10 seconds 
                    window.setTimeout(checkImage, 100);
                } else {
                    primal.wrapperOuter.css("height", ""); //Else remove height attribute
                }
            }

            if ($currentimg.get(0) !== undefined) {
                iterations = 0;
                checkImage();
            } else {
                addHeight();
            }
        },

        completeImg : function (img) {
            var naturalWidthType;

            if (!img.complete) {
                return false;
            }
            naturalWidthType = typeof img.naturalWidth;
            if (naturalWidthType !== "undefined" && img.naturalWidth === 0) {
                return false;
            }
            return true;
        },

        onVisibleItems : function () {
            var primal = this,
                i;

            if (primal.options.addClassActive === true) {
                primal.$rwdItems.removeClass("active");
            }
            primal.visibleItems = [];
            for (i = primal.currentItem; i < primal.currentItem + primal.options.items; i += 1) {
                primal.visibleItems.push(i);

                if (primal.options.addClassActive === true) {
                    $(primal.$rwdItems[i]).addClass("active");
                }
            }
            primal.rwd.visibleItems = primal.visibleItems;
        },

        transitionTypes : function (className) {
            var primal = this;
            //Currently available: "fade", "backSlide", "goDown", "fadeUp"
            primal.outClass = "rwd-" + className + "-out";
            primal.inClass = "rwd-" + className + "-in";
        },

        singleItemTransition : function () {
            var primal = this,
                outClass = primal.outClass,
                inClass = primal.inClass,
                $currentItem = primal.$rwdItems.eq(primal.currentItem),
                $prevItem = primal.$rwdItems.eq(primal.prevItem),
                prevPos = Math.abs(primal.positionsInArray[primal.currentItem]) + primal.positionsInArray[primal.prevItem],
                origin = Math.abs(primal.positionsInArray[primal.currentItem]) + primal.itemWidth / 2,
                animEnd = 'webkitAnimationEnd oAnimationEnd MSAnimationEnd animationend';

            primal.isTransition = true;

            primal.$rwdWrapper
                .addClass('rwd-origin')
                .css({
                    "-webkit-transform-origin" : origin + "px",
                    "-moz-perspective-origin" : origin + "px",
                    "perspective-origin" : origin + "px"
                });
            function transStyles(prevPos) {
                return {
                    "position" : "relative",
                    "left" : prevPos + "px"
                };
            }

            $prevItem
                .css(transStyles(prevPos, 10))
                .addClass(outClass)
                .on(animEnd, function () {
                    primal.endPrev = true;
                    $prevItem.off(animEnd);
                    primal.clearTransStyle($prevItem, outClass);
                });

            $currentItem
                .addClass(inClass)
                .on(animEnd, function () {
                    primal.endCurrent = true;
                    $currentItem.off(animEnd);
                    primal.clearTransStyle($currentItem, inClass);
                });
        },

        clearTransStyle : function (item, classToRemove) {
            var primal = this;
            item.css({
                "position" : "",
                "left" : ""
            }).removeClass(classToRemove);

            if (primal.endPrev && primal.endCurrent) {
                primal.$rwdWrapper.removeClass('rwd-origin');
                primal.endPrev = false;
                primal.endCurrent = false;
                primal.isTransition = false;
            }
        },

        rwdStatus : function () {
            var primal = this;
            primal.rwd = {
                "userOptions"   : primal.userOptions,
                "primalElement"   : primal.$elem,
                "userItems"     : primal.$userItems,
                "rwdItems"      : primal.$rwdItems,
                "currentItem"   : primal.currentItem,
                "prevItem"      : primal.prevItem,
                "visibleItems"  : primal.visibleItems,
                "isTouch"       : primal.browser.isTouch,
                "browser"       : primal.browser,
                "dragDirection" : primal.dragDirection
            };
        },

        clearEvents : function () {
            var primal = this;
            primal.$elem.off(".rwd rwd mousedown.disableTextSelect");
            $(document).off(".rwd rwd");
            $(window).off("resize", primal.resizer);
        },

        unWrap : function () {
            var primal = this;
            if (primal.$elem.children().length !== 0) {
                primal.$rwdWrapper.unwrap();
                primal.$userItems.unwrap().unwrap();
                if (primal.rwdControls) {
                    primal.rwdControls.remove();
                }
            }
            primal.clearEvents();
            primal.$elem
                .attr("style", primal.$elem.data("rwd-originalStyles") || "")
                .attr("class", primal.$elem.data("rwd-originalClasses"));
        },

        destroy : function () {
            var primal = this;
            primal.stop();
            window.clearInterval(primal.checkVisible);
            primal.unWrap();
            primal.$elem.removeData();
        },

        reinit : function (newOptions) {
            var primal = this,
                options = $.extend({}, primal.userOptions, newOptions);
            primal.unWrap();
            primal.init(options, primal.$elem);
        },

        addItem : function (htmlString, targetPosition) {
            var primal = this,
                position;

            if (!htmlString) {return false; }

            if (primal.$elem.children().length === 0) {
                primal.$elem.append(htmlString);
                primal.setVars();
                return false;
            }
            primal.unWrap();
            if (targetPosition === undefined || targetPosition === -1) {
                position = -1;
            } else {
                position = targetPosition;
            }
            if (position >= primal.$userItems.length || position === -1) {
                primal.$userItems.eq(-1).after(htmlString);
            } else {
                primal.$userItems.eq(position).before(htmlString);
            }

            primal.setVars();
        },

        removeItem : function (targetPosition) {
            var primal = this,
                position;

            if (primal.$elem.children().length === 0) {
                return false;
            }
            if (targetPosition === undefined || targetPosition === -1) {
                position = -1;
            } else {
                position = targetPosition;
            }

            primal.unWrap();
            primal.$userItems.eq(position).remove();
            primal.setVars();
        }

    };

    $.fn.rwdCarousel = function (options) {
        return this.each(function () {
            if ($(this).data("rwd-init") === true) {
                return false;
            }
            $(this).data("rwd-init", true);
            var carousel = Object.create(Carousel);
            carousel.init(options, this);
            $.data(this, "rwdCarousel", carousel);
        });
    };

    $.fn.rwdCarousel.options = {

        items : 5,
        itemsCustom : [
            [1200,6],
            [992,5],
            [768,4],
            [400,3],
            [320,1]
        ],
        itemsDesktop : [1199, 4],
        itemsDesktopSmall : [979, 3],
        itemsTablet : [768, 2],
        itemsTabletSmall : false,
        itemsMobile : [479, 1],
        singleItem : false,
        itemsScaleUp : false,

        slideSpeed : 200,
        paginationSpeed : 800,
        rewindSpeed : 1000,

        autoPlay : false,
        stopOnHover : false,

        navigation : false,
        navigationText: ['<div class="owl-arrow owl-prev"></div>','<div class="owl-arrow owl-next"></div>'],
        rewindNav : true,
        scrollPerPage : false,

        pagination : true,
        paginationNumbers : false,

        responsive : true,
        responsiveRefreshRate : 200,
        responsiveprimalWidth : window,

        primalClass : "rwd-carousel",
        theme : "rwd-theme",

        lazyLoad : false,
        lazyFollow : true,
        lazyEffect : "fade",

        autoHeight : false,

        jsonPath : false,
        jsonSuccess : false,

        dragBeforeAnimFinish : true,
        mouseDrag : true,
        touchDrag : true,

        addClassActive : false,
        transitionStyle : false,

        beforeUpdate : false,
        afterUpdate : false,
        beforeInit : false,
        afterInit : false,
        beforeMove : false,
        afterMove : false,
        afterAction : false,
        startDragging : false,
        afterLazyLoad: false
    };
}(jQuery, window, document));