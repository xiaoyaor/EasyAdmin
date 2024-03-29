define(['jquery', 'bootstrap', 'backend', 'addtabs', 'adminlte', 'form', 'bootstrap-tour'], function ($, undefined, Backend, undefined, AdminLTE, Form,Tour) {
    var Controller = {
        index: function () {
            //双击重新加载页面
            $(document).on("dblclick", ".sidebar-menu li > a", function (e) {
                $("#con_" + $(this).attr("addtabs") + " iframe").attr('src', function (i, val) {
                    return val;
                });
                e.stopPropagation();
            });

            //修复在移除窗口时下拉框不隐藏的BUG
            $(window).on("blur", function () {
                $("[data-toggle='dropdown']").parent().removeClass("open");
                if ($("body").hasClass("sidebar-open")) {
                    $(".sidebar-toggle").trigger("click");
                }
            });

            //快捷搜索
            $(".menuresult").width($("form.sidebar-form > .input-group").width());
            var searchResult = $(".menuresult");
            $("form.sidebar-form").on("blur", "input[name=q]", function () {
                searchResult.addClass("hide");
            }).on("focus", "input[name=q]", function () {
                if ($("a", searchResult).size() > 0) {
                    searchResult.removeClass("hide");
                }
            }).on("keyup", "input[name=q]", function () {
                searchResult.html('');
                var val = $(this).val();
                var html = [];
                if (val != '') {
                    $("ul.sidebar-menu li a[addtabs]:not([href^='javascript:;'])").each(function () {
                        if ($("span:first", this).text().indexOf(val) > -1 || $(this).attr("py").indexOf(val) > -1 || $(this).attr("pinyin").indexOf(val) > -1) {
                            html.push('<a data-url="' + $(this).attr("href") + '" href="javascript:;">' + $("span:first", this).text() + '</a>');
                            if (html.length >= 100) {
                                return false;
                            }
                        }
                    });
                }
                $(searchResult).append(html.join(""));
                if (html.length > 0) {
                    searchResult.removeClass("hide");
                } else {
                    searchResult.addClass("hide");
                }
            });
            //快捷搜索点击事件
            $("form.sidebar-form").on('mousedown click', '.menuresult a[data-url]', function () {
                Backend.api.addtabs($(this).data("url"));
            });

            localStorage.setItem("easystep", "installed");
            //读取首次登录推荐插件列表
            if (localStorage.getItem("easystep") === "installed" && Config.autotip === true) {
                $.ajax({
                    url: Config.easyadmin.api_url + '/addins/addon/recommend',
                    type: 'post',
                    dataType: 'jsonp',
                    success: function (ret) {
                        require(['template'], function (Template) {
                            var install = function (name, title) {
                                Easy.api.ajax({
                                    url: 'addon/install',
                                    data: {name: name, eaversion: Config.easyadmin.version}
                                }, function (data, ret) {
                                    Easy.api.refreshmenu();
                                });
                            };
                            $(document).on('click', '.btn-install', function () {
                                $(this).prop("disabled", true).addClass("disabled");
                                $("input[name=addon]:checked").each(function () {
                                    install($(this).data("name"));
                                });
                                return false;
                            });
                            $(document).on('click', '.btn-notnow', function () {
                                Layer.closeAll();
                            });
                            Layer.open({
                                title: ret.title,
                                area: 'auto',      //宽高自适应
                                maxWidth: '1000px',
                                maxHeight:'800px',    //最大高度800像素
                                content: Template.render(ret.tpl, {addonlist: ret.rows})
                            });
                            localStorage.setItem("easystep", "dashboard");
                        });
                    }
                });
            }

            //版本检测
            var checkupdate = function (ignoreversion, tips) {
                $.ajax({
                    url: Config.easyadmin.api_url + '/addins/version/check',
                    type: 'post',
                    data: {version: Config.easyadmin.version},
                    dataType: 'jsonp',
                    success: function (ret) {
                        if (ret.data && ignoreversion !== ret.data.newversion) {
                            Layer.open({
                                title: __('Discover new version'),
                                maxHeight: 400,
                                content: '<h5 style="background-color:#f7f7f7; font-size:14px; padding: 10px;">' + __('Your current version') + ':' + ret.data.version + '，' + __('New version') + ':' + ret.data.newversion + '</h5><span class="label label-danger">' + __('Release notes') + '</span><br/>' + ret.data.upgradetext,
                                btn: [__('Go to download'), __('Ignore this version'), __('Do not remind again')],
                                btn2: function (index, layero) {
                                    localStorage.setItem("ignoreversion", ret.data.newversion);
                                },
                                btn3: function (index, layero) {
                                    localStorage.setItem("ignoreversion", "*");
                                },
                                success: function (layero, index) {
                                    $(".layui-layer-btn0", layero).attr("href", ret.data.downloadurl).attr("target", "_blank");
                                }
                            });
                        } else {
                            if (tips) {
                                Toastr.success(__('Currently is the latest version'));
                            }
                        }
                    }, error: function (e) {
                        if (tips) {
                            Toastr.error(__('Unknown data format') + ":" + e.message);
                        }
                    }
                });
            };

            //读取版本检测信息
            var ignoreversion = localStorage.getItem("ignoreversion");
            if (Config.easyadmin.checkupdate && ignoreversion !== "*") {
                checkupdate(ignoreversion, false);
            }
            //手动检测版本信息
            $("a[data-toggle='checkupdate']").on('click', function () {
                checkupdate('', true);
            });

            //切换左侧sidebar显示隐藏
            $(document).on("click fa.event.toggleitem", ".sidebar-menu li > a", function (e) {
                $(".sidebar-menu li").removeClass("active");
                //当外部触发隐藏的a时,触发父辈a的事件
                if (!$(this).closest("ul").is(":visible")) {
                    //如果不需要左侧的菜单栏联动可以注释下面一行即可
                    $(this).closest("ul").prev().trigger("click");
                }

                var visible = $(this).next("ul").is(":visible");
                if (!visible) {
                    $(this).parents("li").addClass("active");
                } else {
                }
                e.stopPropagation();
            });

            //切换左侧sidebar显示隐藏
            $(document).on('click', ".btn-sidebar_collapse", function () {
                $.ajax({
                    url: 'ajax/sidebar_collapse',
                    method:'post',
                    dataType: 'json',
                    data: {type: $(this).data("type")},
                    cache: false,
                    success: function (ret) {
                        if (ret.hasOwnProperty("code")) {
                            if (ret.code === 1) {
                                console.log(ret.msg);
                            } else {
                                console.log(ret.msg);
                            }
                        } else {
                            Toastr.error(__('Unknown data format'));
                        }
                    }, error: function () {
                        Toastr.error(__('Network error'));
                    }
                });
            });

            //清除缓存
            $(document).on('click', "ul.wipecache li a", function () {
                $.ajax({
                    url: 'ajax/wipecache',
                    dataType: 'json',
                    data: {type: $(this).data("type")},
                    cache: false,
                    success: function (ret) {
                        if (ret.hasOwnProperty("code")) {
                            var msg = ret.hasOwnProperty("msg") && ret.msg != "" ? ret.msg : "";
                            if (ret.code === 1) {
                                Toastr.success(msg ? msg : __('Wipe cache completed'));
                            } else {
                                Toastr.error(msg ? msg : __('Wipe cache failed'));
                            }
                        } else {
                            Toastr.error(__('Unknown data format'));
                        }
                    }, error: function () {
                        Toastr.error(__('Network error'));
                    }
                });
            });

            //全屏事件
            $(document).on('click', "[data-toggle='fullscreen']", function () {
                var doc = document.documentElement;
                if ($(document.body).hasClass("full-screen")) {
                    $(document.body).removeClass("full-screen");
                    document.exitFullscreen ? document.exitFullscreen() : document.mozCancelFullScreen ? document.mozCancelFullScreen() : document.webkitExitFullscreen && document.webkitExitFullscreen();
                } else {
                    $(document.body).addClass("full-screen");
                    doc.requestFullscreen ? doc.requestFullscreen() : doc.mozRequestFullScreen ? doc.mozRequestFullScreen() : doc.webkitRequestFullscreen ? doc.webkitRequestFullscreen() : doc.msRequestFullscreen && doc.msRequestFullscreen();
                }
            });

            var multiplenav = Config.multiplenav;
            var firstnav = $("#firstnav .nav-addtabs");
            var nav = multiplenav ? $("#secondnav .nav-addtabs") : firstnav;

            //刷新菜单事件
            $(document).on('refresh', '.sidebar-menu', function () {
                Easy.api.ajax({
                    url: 'index/index',
                    data: {action: 'refreshmenu'}
                }, function (data) {
                    $(".sidebar-menu li:not([data-rel='external'])").remove();
                    $(".sidebar-menu").prepend(data.menulist);
                    if (multiplenav) {
                        firstnav.html(data.navlist);
                    }
                    $("li[role='presentation'].active a", nav).trigger('click');
                    return false;
                }, function () {
                    return false;
                });
            });

            if (multiplenav) {
                //一级菜单自适应
                $(window).resize(function () {
                    var siblingsWidth = 0;
                    firstnav.siblings().each(function () {
                        siblingsWidth += $(this).outerWidth();
                    });
                    firstnav.width(firstnav.parent().width() - siblingsWidth);
                    firstnav.refreshAddtabs();
                });

                //点击顶部第一级菜单栏
                firstnav.on("click", "li a", function () {
                    $("li", firstnav).removeClass("active");
                    $(this).closest("li").addClass("active");
                    $(".sidebar-menu > li.treeview").addClass("hidden");
                    if ($(this).attr("url") == "javascript:;") {
                        var sonlist = $(".sidebar-menu > li[pid='" + $(this).attr("addtabs") + "']");
                        sonlist.removeClass("hidden");
                        var sidenav;
                        var last_id = $(this).attr("last-id");
                        if (last_id) {
                            sidenav = $(".sidebar-menu > li[pid='" + $(this).attr("addtabs") + "'] a[addtabs='" + last_id + "']");
                        } else {
                            sidenav = $(".sidebar-menu > li[pid='" + $(this).attr("addtabs") + "']:first > a");
                        }
                        if (sidenav) {
                            sidenav.attr("href") != "javascript:;" && sidenav.trigger('click');
                        }
                    } else {

                    }
                });

                //点击左侧菜单栏
                $(document).on('click', '.sidebar-menu li a[addtabs]', function (e) {
                    var parents = $(this).parentsUntil("ul.sidebar-menu", "li");
                    var top = parents[parents.length - 1];
                    var pid = $(top).attr("pid");
                    if (pid) {
                        var obj = $("li a[addtabs=" + pid + "]", firstnav);
                        var last_id = obj.attr("last-id");
                        if (!last_id || last_id != pid) {
                            obj.attr("last-id", $(this).attr("addtabs"));
                            if (!obj.closest("li").hasClass("active")) {
                                obj.trigger("click");
                            }
                        }
                    }
                });

                var mobilenav = $(".mobilenav");
                $("#firstnav .nav-addtabs li a").each(function () {
                    mobilenav.append($(this).clone().addClass("btn btn-app"));
                });

                //点击移动端一级菜单
                mobilenav.on("click", "a", function () {
                    $("a", mobilenav).removeClass("active");
                    $(this).addClass("active");
                    $(".sidebar-menu > li.treeview").addClass("hidden");
                    if ($(this).attr("url") == "javascript:;") {
                        var sonlist = $(".sidebar-menu > li[pid='" + $(this).attr("addtabs") + "']");
                        sonlist.removeClass("hidden");
                    }
                });
            }

            //这一行需要放在点击左侧链接事件之前
            var addtabs = Config.referer ? localStorage.getItem("addtabs") : null;

            //绑定tabs事件,如果需要点击强制刷新iframe,则请将iframeForceRefresh置为true,iframeForceRefreshTable只强制刷新表格
            nav.addtabs({iframeHeight: "100%", iframeForceRefresh: false, iframeForceRefreshTable: true, nav: nav});

            if ($("ul.sidebar-menu li.active a").size() > 0) {
                $("ul.sidebar-menu li.active a").trigger("click");
            } else {
                if (Config.multiplenav) {
                    $("li:first > a", firstnav).trigger("click");
                } else {
                    $("ul.sidebar-menu li a[url!='javascript:;']:first").trigger("click");
                }
            }

            //如果是刷新操作则直接返回刷新前的页面
            if (Config.referer) {
                if (Config.referer === $(addtabs).attr("url")) {
                    var active = $("ul.sidebar-menu li a[addtabs=" + $(addtabs).attr("addtabs") + "]");
                    if (multiplenav && active.size() == 0) {
                        active = $("ul li a[addtabs='" + $(addtabs).attr("addtabs") + "']");
                    }
                    if (active.size() > 0) {
                        active.trigger("click");
                    } else {
                        $(addtabs).appendTo(document.body).addClass("hide").trigger("click");
                    }
                } else {
                    //刷新页面后跳到到刷新前的页面
                    Backend.api.addtabs(Config.referer);
                }
            }

            var my_skins = [
                "skin-blue",
                "skin-black",
                "skin-red",
                "skin-yellow",
                "skin-purple",
                "skin-green",
                "skin-blue-light",
                "skin-white-light",
                "skin-red-light",
                "skin-yellow-light",
                "skin-purple-light",
                "skin-green-light",

                "skin-blue-full",
                "skin-silver-full",
                "skin-red-full",
                "skin-yellow-full",
                "skin-purple-full",
                "skin-green-full",
                "skin-season",
                "skin-snow",
                "skin-mountain",
                "skin-universe",
                "skin-forest",
                "skin-earth",
            ];
            setup();

            function change_layout(cls) {
                $("body").toggleClass(cls);
                AdminLTE.layout.fixSidebar();
                //Fix the problem with right sidebar and layout boxed
                if (cls == "layout-boxed")
                    AdminLTE.controlSidebar._fix($(".control-sidebar-bg"));
                if ($('body').hasClass('fixed') && cls == 'fixed') {
                    AdminLTE.pushMenu.expandOnHover();
                    AdminLTE.layout.activate();
                }
                AdminLTE.controlSidebar._fix($(".control-sidebar-bg"));
                AdminLTE.controlSidebar._fix($(".control-sidebar"));
            }

            function change_skin(cls) {
                if (!$("body").hasClass(cls)) {
                    $("body").removeClass(my_skins.join(' ')).addClass(cls);
                    //localStorage.setItem('skin', cls);
                    var cssfile = Config.site.cdnurl + "/assets/css/skins/" + cls + ".css";
                    $('head').append('<link rel="stylesheet" href="' + cssfile + '" type="text/css" />');
                    send_skin(cls);
                }
                return false;
            }

            function send_skin(cls) {
                $.ajax({
                    url: Config.moduleurl + '/index/changeSkin',
                    type: 'post',
                    data: {'skin': cls},
                    dataType: 'json',
                    success: function (ret) {
                        console.log(ret.msg)
                    },
                    error: function (e) {
                        console.log(e.msg)
                    }
                });
                return false;
            }

            function setup() {

                ////取消localStorage存储的skin信息
                // var tmp = localStorage.getItem('skin');
                // if (tmp && $.inArray(tmp, my_skins) != -1)
                //     change_skin(tmp);

                // 皮肤切换
                $("[data-skin]").on('click', function (e) {
                    if ($(this).hasClass('knob'))
                        return;
                    e.preventDefault();
                    change_skin($(this).data('skin'));
                });

                // 布局切换
                $("[data-layout]").on('click', function () {
                    change_layout($(this).data('layout'));
                });

                // 切换子菜单显示和菜单小图标的显示
                $("[data-menu]").on('click', function () {
                    if ($(this).data("menu") == 'show-submenu') {
                        $("ul.sidebar-menu").toggleClass("show-submenu");
                    } else {
                        nav.toggleClass("disable-top-badge");
                    }
                });

                // 右侧控制栏切换
                $("[data-controlsidebar]").on('click', function () {
                    change_layout($(this).data('controlsidebar'));
                    var slide = !AdminLTE.options.controlSidebarOptions.slide;
                    AdminLTE.options.controlSidebarOptions.slide = slide;
                    if (!slide)
                        $('.control-sidebar').removeClass('control-sidebar-open');
                });

                // 右侧控制栏背景切换
                $("[data-sidebarskin='toggle']").on('click', function () {
                    var sidebar = $(".control-sidebar");
                    if (sidebar.hasClass("control-sidebar-dark")) {
                        sidebar.removeClass("control-sidebar-dark")
                        sidebar.addClass("control-sidebar-light")
                    } else {
                        sidebar.removeClass("control-sidebar-light")
                        sidebar.addClass("control-sidebar-dark")
                    }
                });

                // 菜单栏展开或收起
                $("[data-enable='expandOnHover']").on('click', function () {
                    $(this).attr('disabled', true);
                    AdminLTE.pushMenu.expandOnHover();
                    if (!$('body').hasClass('sidebar-collapse'))
                        $("[data-layout='sidebar-collapse']").click();
                });

                // 重设选项
                if ($('body').hasClass('fixed')) {
                    $("[data-layout='fixed']").attr('checked', 'checked');
                }
                if ($('body').hasClass('layout-boxed')) {
                    $("[data-layout='layout-boxed']").attr('checked', 'checked');
                }
                if ($('body').hasClass('sidebar-collapse')) {
                    $("[data-layout='sidebar-collapse']").attr('checked', 'checked');
                }
                if ($('ul.sidebar-menu').hasClass('show-submenu')) {
                    $("[data-menu='show-submenu']").attr('checked', 'checked');
                }
                if (nav.hasClass('disable-top-badge')) {
                    $("[data-menu='disable-top-badge']").attr('checked', 'checked');
                }
            }
            //首页提示
            $(function() {
                var $demo, duration, remaining, tour;
                $demo = $("#demo");
                duration = false;
                remaining = duration;
                tour = new Tour({
                    backdrop: true,
                    backdropContainer: 'body',
                    backdropPadding: 0,
                    onStart: function() {
                        return $demo.addClass("disabled", true);
                    },
                    onEnd: function() {
                        return $demo.removeClass("disabled", true);
                    },
                    debug: true,
                    steps: [
                        {
                            element: "#firstnav",
                            placement: "bottom",
                            title: "安装引导",
                            content: "导航栏"
                        },{
                            element: "#secondnav",
                            placement: "bottom",
                            title: "安装引导",
                            content: "当前打开窗口"
                        },{
                            element: ".fa-toggle-on",
                            placement: "bottom",
                            title: "安装引导",
                            content: "切换顶级菜单"
                        },{
                            element: ".fa-sitemap",
                            placement: "bottom",
                            title: "安装引导二",
                            content: "网址列表"
                        },{
                            element: ".fa-arrows-alt",
                            placement: "bottom",
                            title: "安装引导三",
                            content: "全屏切换"
                        }
                    ],
                    template:"<div class='popover'><div class='arrow'></div><h3 class='popover-title'></h3><div class='popover-content'></div><div class='popover-navigation'>" +
                        "<div class='btn-group'><button class='btn btn-sm btn-default' data-role='prev'>« 上一步</button><button class='btn btn-sm btn-default' data-role='next'>下一步 »</button>" +
                        "<button class='btn btn-sm btn-default' data-role='pause-resume' data-pause-text='Pause' data-resume-text='Resume'>暂停</button></div><button class='btn btn-sm btn-default' data-role='end'>知道了</button></div></div>"
                });

                // Initialize the tour
                tour.init();

                // Start the tour
                tour.start();

                $(".fa-question-circle-o").on('click',function(){
                    tour.restart();
                })
            });

            $(window).resize();

        }
    };

    return Controller;
});