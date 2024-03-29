define(['jquery', 'bootstrap', 'backend', 'table','clipboard', 'form', 'template', 'bootstrap-tour'], function ($, undefined, Backend, Table, clipboard, Form, Template,Tour) {
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: Config.easyadmin.api_url + '/addins/addon',
                    add_url: '',
                    edit_url: '',
                    del_url: '',
                    multi_url: ''
                }
            });

            var table = $("#table");

            table.on('load-success.bs.table', function (e, json) {
                if (json && typeof json.category != 'undefined' && $(".nav-category li").size() == 2) {
                    $.each(json.category, function (i, j) {
                        $("<li><a href='javascript:;' data-id='" + j.id + "'>" + j.name + "</a></li>").insertBefore($(".nav-category li:last"));
                    });
                }
            });
            table.on('load-error.bs.table', function (e, status, res) {
                if (status == 404 && $(".btn-switch.active").data("type") != "local") {
                    Layer.confirm(__('Store now available tips'), {
                        title: __('Warmtips'),
                        btn: [__('Switch to the local'), __('Try to reload')]
                    }, function (index) {
                        layer.close(index);
                        $(".btn-switch[data-type='local']").trigger("click");
                    }, function (index) {
                        layer.close(index);
                        table.bootstrapTable('refresh');
                    });
                    return false;
                }
            });
            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                var parenttable = table.closest('.bootstrap-table');
                var d = $(".fixed-table-toolbar", parenttable).find(".search input");
                d.off("keyup drop blur");
                d.on("keyup", function (e) {
                    if (e.keyCode == 13) {
                        var that = this;
                        var options = table.bootstrapTable('getOptions');
                        var queryParams = options.queryParams;
                        options.pageNumber = 1;
                        options.queryParams = function (params) {
                            var params = queryParams(params);
                            params.search = $(that).val();
                            return params;
                        };
                        table.bootstrapTable('refresh', {});
                    }
                });
            });

            Template.helper("Moment", Moment);
            Template.helper("addons", Config['addons']);

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                queryParams: function (params) {
                    var userinfo = Controller.api.userinfo.get();
                    $.extend(params, {
                        uid: userinfo ? userinfo.id : '',
                        token: userinfo ? userinfo.token : '',
                        version: Config.easyadmin.version
                    });
                    return params;
                },
                escape: false,
                columns: [
                    [
                        {field: 'id', title: 'ID', operate: false, visible: false},
                        {
                            field: 'home',
                            title: __('Index'),
                            width: '50px',
                            formatter: Controller.api.formatter.home
                        },
                        {
                            field: 'name',
                            title: __('Name'),
                            operate: false,
                            align: 'left',
                            visible: true,
                            width: '120px'
                        },
                        {
                            field: 'title',
                            title: __('Title'),
                            operate: 'LIKE',
                            align: 'left',
                            formatter: Controller.api.formatter.title
                        },
                        {
                            field: 'intro',
                            title: __('Intro'),
                            operate: 'LIKE',
                            align: 'left',
                            class: 'visible-lg'
                        },
                        {
                            field: 'author',
                            title: __('Author'),
                            operate: 'LIKE',
                            width: '100px',
                            formatter: Controller.api.formatter.author
                        },
                        {
                            field: 'price',
                            title: __('Price'),
                            operate: 'LIKE',
                            width: '100px',
                            align: 'center',
                            formatter: Controller.api.formatter.price
                        },
                        {
                            field: 'downloads',
                            title: __('Downloads'),
                            operate: 'LIKE',
                            width: '80px',
                            align: 'center',
                            formatter: Controller.api.formatter.downloads
                        },
                        {
                            field: 'version',
                            title: __('Version'),
                            operate: 'LIKE',
                            width: '80px',
                            align: 'center',
                            formatter: Controller.api.formatter.version
                        },
                        {
                            field: 'toggle',
                            title: __('Status'),
                            width: '80px',
                            formatter: Controller.api.formatter.toggle
                        },
                        {
                            field: 'id',
                            title: __('Operate'),
                            align: 'right',
                            table: table,
                            formatter: Controller.api.formatter.operate
                        },
                    ]
                ],
                responseHandler: function (res) {
                    $.each(res.rows, function (i, j) {
                        j.addon = typeof Config.addons[j.name] != 'undefined' ? Config.addons[j.name] : null;
                    });
                    return res;
                },
                dataType: 'jsonp',
                templateView: false,
                clickToSelect: false,
                search: true,
                showColumns: false,
                showToggle: false,
                showExport: false,
                showSearch: false,
                commonSearch: true,
                searchFormVisible: true,
                searchFormTemplate: 'searchformtpl',
                pageSize: 30,
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            // 离线安装
            require(['upload'], function (Upload) {
                Upload.api.plupload("#plupload-addon", function (data, ret) {
                    if ( data.appmodule === 1 ){
                        Layer.open({
                            content: Template("apptpl", ret.data),
                            zIndex: 99,
                            area: ['400px', '300px'],
                            title: __('Select Module Install'),
                            resize: false,
                            btn: [__('Ok'), __('Cancel')],
                            yes: function (index, layero) {
                                Easy.api.ajax({
                                    url: Config.moduleurl + '/addon/appmodule',
                                    dataType: 'json',
                                    data: {
                                        app: $("#app", layero).val(),
                                        _method: 'POST',
                                        data:data,
                                    }
                                }, function (data, ret) {
                                    Config['addons'][data.addon.name] = data.addon;
                                    operate(data.addon.name, 'enable', false);
                                }, function (data, ret) {
                                    return false;
                                });
                            },
                            btn2: function () {
                                Layer.closeAll();
                                return false;
                            }
                        });
                    }else{
                        Config['addons'][data.addon.name] = data.addon;
                        Toastr.success(ret.msg);
                        operate(data.addon.name, 'enable', false);
                    }
                });
            });

            // 查看插件首页
            $(document).on("click", ".btn-addonindex", function () {
                if ($(this).attr("href") == 'javascript:;') {
                    Layer.msg(__('Not installed tips'), {icon: 7});
                } else if ($(this).closest(".operate").find("a.btn-enable").size() > 0) {
                    Layer.msg(__('Not enabled tips'), {icon: 7});
                    return false;
                }
            });

            // 切换
            $(document).on("click", ".btn-switch", function () {
                $(".btn-switch").removeClass("active");
                $(this).addClass("active");
                $("form.form-commonsearch input[name='type']").val($(this).data("type"));
                table.bootstrapTable('refresh', {url: $(this).data("url"), pageNumber: 1});
                return false;
            });
            $(document).on("click", ".nav-category li a", function () {
                $(".nav-category li").removeClass("active");
                $(this).parent().addClass("active");
                $("form.form-commonsearch input[name='category_id']").val($(this).data("id"));
                table.bootstrapTable('refresh', {url: $(this).data("url"), pageNumber: 1});
                return false;
            });

            // 会员信息
            $(document).on("click", ".btn-userinfo", function () {
                var that = this;
                var userinfo = Controller.api.userinfo.get();
                if (!userinfo) {
                    Layer.open({
                        content: Template("logintpl", {}),
                        zIndex: 99,
                        area: ['430px', '350px'],
                        title: __('Login EasyAdmin'),
                        resize: false,
                        btn: [__('Login'), __('Register')],
                        yes: function (index, layero) {
                            Easy.api.ajax({
                                url: Config.easyadmin.api_url + '/user/index/login',
                                dataType: 'jsonp',
                                data: {
                                    account: $("#inputAccount", layero).val(),
                                    password: $("#inputPassword", layero).val(),
                                    _method: 'POST'
                                }
                            }, function (data, ret) {
                                Controller.api.userinfo.set(data);
                                Layer.closeAll();
                                Layer.alert(ret.msg);
                            }, function (data, ret) {
                            });
                        },
                        btn2: function () {
                            return false;
                        },
                        success: function (layero, index) {
                            $(".layui-layer-btn1", layero).prop("href", Config.easyadmin.url+"/user/register.html").prop("target", "_blank");
                        }
                    });
                } else {
                    Easy.api.ajax({
                        url: Config.easyadmin.api_url + '/user/index/index',
                        dataType: 'jsonp',
                        data: {
                            user_id: userinfo.id,
                            token: userinfo.token,
                        }
                    }, function (data) {
                        Layer.open({
                            content: Template("userinfotpl", userinfo),
                            area: ['430px', '360px'],
                            title: __('Userinfo'),
                            resize: false,
                            btn: [__('Logout'), __('Cancel')],
                            yes: function () {
                                Easy.api.ajax({
                                    url: Config.easyadmin.api_url + '/user/index/logout',
                                    dataType: 'jsonp',
                                    data: {uid: userinfo.id, token: userinfo.token}
                                }, function (data, ret) {
                                    Controller.api.userinfo.set(null);
                                    Layer.closeAll();
                                    Layer.alert(ret.msg);
                                }, function (data, ret) {
                                    Controller.api.userinfo.set(null);
                                    Layer.closeAll();
                                    Layer.alert(ret.msg);
                                });
                            }
                        });
                        return false;
                    }, function (data) {
                        Controller.api.userinfo.set(null);
                        $(that).trigger('click');
                        return false;
                    });

                }
            });

            //安装提示
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
                                element: "a[data-id=1]",
                                placement: "bottom",
                                title: "安装引导一",
                                content: "请先安装<a style='color: red'>基础插件</a>下的<a style='color: red'>所有插件</a>。按照插件依赖顺序依次安装，安装完<a style='color: red'>权限管理插件</a>后需要进行登录验证，登录后再继续安装剩余插件"
                            },{
                                element: "a[data-id=7]",
                                placement: "bottom",
                                title: "安装引导二",
                                content: "<a style='color: red'>完整应用</a>下的所有插件包含完整的前后台，安装前请安装相应的依赖插件，每个完整应用都可作为一个完整网站运营"
                            },{
                                element: "a[data-id=3]",
                                placement: "bottom",
                                title: "安装引导三",
                                content: "<a style='color: red'>开发工具</a>下的所有插件用于插件开发，easyadmin提供了功能丰富的插件开发插件，使开发更简单方便"
                            },{
                                element: ".bootstrap-table",
                                placement: "top",
                                title: "安装引导四",
                                content: "<a style='color: red'>插件列表</a>，提供插件安装、升级、卸载服务"
                            },{
                                element: ".plupload",
                                placement: "top",
                                title: "安装引导五",
                                content: "<a style='color: red'>离线安装</a>，安装离线插件"
                            },{
                                element: "a[data-url='addon/downloaded']",
                                placement: "top",
                                title: "安装引导六",
                                content: "<a style='color: red'>本地插件</a>，查看本地安装的所有插件"
                            },{
                                element: ".btn-userinfo",
                                placement: "top",
                                title: "安装引导七",
                                content: "<a style='color: red'>登录会员</a>，登录easyadmin官方会员，提供付费下载服务"
                            },
                        ],
                        template:"<div class='popover'><div class='arrow'></div><h3 class='popover-title'></h3><div class='popover-content'></div><div class='popover-navigation'>" +
                            "<div class='btn-group'><button class='btn btn-sm btn-default' data-role='prev'>« 上一步</button><button class='btn btn-sm btn-default' data-role='next'>下一步 »</button>" +
                            "<button class='btn btn-sm btn-default' data-role='pause-resume' data-pause-text='Pause' data-resume-text='Resume'>暂停</button></div><button class='btn btn-sm btn-default' data-role='end'>知道了</button></div></div>"
                    }
                );

                // Initialize the tour
                tour.init();

                // Start the tour
                tour.start();

                $(".btn-guide").on('click',function(){
                    tour.restart();
                });
            });

            var install = function (name, version, force) {
                var userinfo = Controller.api.userinfo.get();
                var uid = userinfo ? userinfo.id : 0;
                var token = userinfo ? userinfo.token : '';
                // 应用模块
                if (name.substring(0,4) === 'app_'){
                    Easy.api.ajax({
                        url: 'addon/applist',
                        data: {
                            name: name,
                        }
                    },function (data, ret) {

                        Layer.open({
                            content: Template("apptpl", ret.data),
                            zIndex: 99,
                            area: ['400px', '300px'],
                            title: __('Select Module Install'),
                            resize: false,
                            btn: [__('Ok'), __('Cancel')],
                            yes: function (index, layero) {
                                Easy.api.ajax({
                                    url: 'addon/install',
                                    data: {
                                        name: name,
                                        force: force ? 1 : 0,
                                        app: $("#app", layero).val(),
                                        uid: uid,
                                        token: token,
                                        version: version,
                                        eaversion: Config.easyadmin.version
                                    }
                                }, function (data, ret) {
                                    Layer.closeAll();
                                    Config['addons'][data.addon.name] = ret.data.addon;
                                    Layer.alert(__('Online installed tips'), {
                                        btn: [__('OK')],
                                        title: __('Warning'),
                                        icon: 1
                                    });
                                    $('.btn-refresh').trigger('click');
                                    Easy.api.refreshmenu();
                                }, function (data, ret) {
                                    //如果是需要购买的插件则弹出二维码提示
                                    if (ret && ret.code === -1) {
                                        //扫码支付
                                        Layer.open({
                                            content: Template("paytpl", ret.data),
                                            shade: 0.8,
                                            area: ['800px', '600px'],
                                            skin: 'layui-layer-msg layui-layer-pay',
                                            title: false,
                                            closeBtn: true,
                                            btn: false,
                                            resize: false,
                                            end: function () {
                                                Layer.alert(__('Pay tips'));
                                            }
                                        });
                                    } else if (ret && ret.code === -2) {
                                        //如果登录已经超时,重新提醒登录
                                        if (uid && uid != ret.data.uid) {
                                            Controller.api.userinfo.set(null);
                                            $(".operate[data-name='" + name + "'] .btn-install").trigger("click");
                                            return;
                                        }
                                        top.Easy.api.open(ret.data.payurl, __('Pay now'), {
                                            //area: ["650px", "700px"],
                                            area: ["800px", "600px"],
                                            end: function () {
                                                top.Layer.alert(__('Pay tips'));
                                            }
                                        });
                                    } else if (ret && ret.code === -3) {
                                        //插件目录发现影响全局的文件
                                        Layer.open({
                                            content: Template("conflicttpl", ret.data),
                                            shade: 0.8,
                                            area: ['800px', '600px'],
                                            title: __('Warning'),
                                            btn: [__('Continue install'), __('Cancel')],
                                            end: function () {

                                            },
                                            yes: function () {
                                                install(name, version, true);
                                            }
                                        });

                                    } else {
                                        Layer.alert(ret.msg);
                                    }
                                    return false;
                                });

                            },
                            btn2: function () {
                                Layer.closeAll();
                                return false;
                            }
                        });
                    },{

                    });


                }else{
                    Easy.api.ajax({
                        url: 'addon/install',
                        data: {
                            name: name,
                            force: force ? 1 : 0,
                            uid: uid,
                            token: token,
                            version: version,
                            eaversion: Config.easyadmin.version
                        }
                    }, function (data, ret) {
                        Layer.closeAll();
                        Config['addons'][data.addon.name] = ret.data.addon;
                        Layer.alert(__('Online installed tips'), {
                            btn: [__('OK')],
                            title: __('Warning'),
                            icon: 1
                        });
                        $('.btn-refresh').trigger('click');
                        Easy.api.refreshmenu();
                    }, function (data, ret) {
                        //如果是需要购买的插件则弹出二维码提示
                        if (ret && ret.code === -1) {
                            //扫码支付
                            Layer.open({
                                content: Template("paytpl", ret.data),
                                shade: 0.8,
                                area: ['800px', '600px'],
                                skin: 'layui-layer-msg layui-layer-pay',
                                title: false,
                                closeBtn: true,
                                btn: false,
                                resize: false,
                                end: function () {
                                    Layer.alert(__('Pay tips'));
                                }
                            });
                        } else if (ret && ret.code === -2) {
                            //如果登录已经超时,重新提醒登录
                            if (uid && uid != ret.data.uid) {
                                Controller.api.userinfo.set(null);
                                $(".operate[data-name='" + name + "'] .btn-install").trigger("click");
                                return;
                            }
                            top.Easy.api.open(ret.data.payurl, __('Pay now'), {
                                //area: ["650px", "700px"],
                                area: ["800px", "600px"],
                                end: function () {
                                    top.Layer.alert(__('Pay tips'));
                                }
                            });
                        } else if (ret && ret.code === -3) {
                            //插件目录发现影响全局的文件
                            Layer.open({
                                content: Template("conflicttpl", ret.data),
                                shade: 0.8,
                                area: ['800px', '600px'],
                                title: __('Warning'),
                                btn: [__('Continue install'), __('Cancel')],
                                end: function () {

                                },
                                yes: function () {
                                    install(name, version, true);
                                }
                            });

                        } else {
                            Layer.alert(ret.msg);
                        }
                        return false;
                    });
                }
            };

            var uninstall = function (name, force) {
                Easy.api.ajax({
                    url: 'addon/uninstall',
                    data: {name: name, force: force ? 1 : 0}
                }, function (data, ret) {
                    delete Config['addons'][name];
                    Layer.closeAll();
                    $('.btn-refresh').trigger('click');
                    Easy.api.refreshmenu();
                }, function (data, ret) {
                    if (ret && ret.code === -3) {
                        //插件目录发现影响全局的文件
                        Layer.open({
                            content: Template("conflicttpl", ret.data),
                            shade: 0.8,
                            area: ['800px', '600px'],
                            title: __('Warning'),
                            btn: [__('Continue uninstall'), __('Cancel')],
                            end: function () {

                            },
                            yes: function () {
                                uninstall(name, true);
                            }
                        });

                    } else {
                        Layer.alert(ret.msg);
                    }
                    return false;
                });
            };

            var operate = function (name, action, force) {
                Easy.api.ajax({
                    url: 'addon/state',
                    data: {name: name, action: action, force: force ? 1 : 0}
                }, function (data, ret) {
                    var addon = Config['addons'][name];
                    addon.state = action === 'enable' ? 1 : 0;
                    Layer.closeAll();
                    $('.btn-refresh').trigger('click');
                    Easy.api.refreshmenu();
                }, function (data, ret) {
                    if (ret && ret.code === -3) {
                        //插件目录发现影响全局的文件
                        Layer.open({
                            content: Template("conflicttpl", ret.data),
                            shade: 0.8,
                            area: ['800px', '600px'],
                            title: __('Warning'),
                            btn: [__('Continue operate'), __('Cancel')],
                            end: function () {

                            },
                            yes: function () {
                                operate(name, action, true);
                            }
                        });

                    } else {
                        Layer.alert(ret.msg);
                    }
                    return false;
                });
            };

            var upgrade = function (name, version) {
                var userinfo = Controller.api.userinfo.get();
                var uid = userinfo ? userinfo.id : 0;
                var token = userinfo ? userinfo.token : '';
                Easy.api.ajax({
                    url: 'addon/upgrade',
                    data: {name: name, uid: uid, token: token, version: version, eaversion: Config.easyadmin.version}
                }, function (data, ret) {
                    Config['addons'][name].version = version;
                    Layer.closeAll();
                    $('.btn-refresh').trigger('click');
                    Easy.api.refreshmenu();
                }, function (data, ret) {
                    Layer.alert(ret.msg);
                    return false;
                });
            };

            // 点击安装
            $(document).on("click", ".btn-install", function () {
                var that = this;
                var name = $(this).closest(".operate").data("name");
                var version = $(this).data("version");

                var userinfo = Controller.api.userinfo.get();
                var uid = userinfo ? userinfo.id : 0;

                if ($(that).data("type") !== 'free') {
                    if (parseInt(uid) === 0) {
                        return Layer.alert(__('Not login tips'), {
                            title: __('Warning'),
                            btn: [__('Login now'), __('Continue install')],
                            yes: function (index, layero) {
                                $(".btn-userinfo").trigger("click");
                            },
                            btn2: function () {
                                install(name, version, false);
                            }
                        });
                    }
                }
                install(name, version, false);
            });

            // 点击卸载
            $(document).on("click", ".btn-uninstall", function () {
                var name = $(this).closest(".operate").data('name');
                if (Config['addons'][name].state == 1) {
                    Layer.alert(__('Please disable addon first'), {icon: 7});
                    return false;
                }
                Layer.confirm(__('Uninstall tips', Config['addons'][name].title), function () {
                    uninstall(name, false);
                });
            });

            // 点击配置
            $(document).on("click", ".btn-config", function () {
                var name = $(this).closest(".operate").data("name");
                Easy.api.open("addon/config?name=" + name, __('Setting'));
            });

            // 依赖插件
            $(document).on("click", ".btn-warning", function () {
                var name = $(this).closest(".operate").data("name");
                //Easy.api.open("addon/addons?name=" + name, __('Setting'));
            });

            // 点击启用/禁用
            $(document).on("click", ".btn-enable,.btn-disable", function () {
                var name = $(this).data("name");
                var action = $(this).data("action");
                operate(name, action, false);
            });

            // 点击升级
            $(document).on("click", ".btn-upgrade", function () {
                var name = $(this).closest(".operate").data('name');
                if (Config['addons'][name].state == 1) {
                    Layer.alert(__('Please disable addon first'), {icon: 7});
                    return false;
                }
                var version = $(this).data("version");

                Layer.confirm(__('Upgrade tips', Config['addons'][name].title), function () {
                    upgrade(name, version);
                });
            });

            $(document).on("click", ".operate .btn-group .dropdown-toggle", function () {
                $(this).closest(".btn-group").toggleClass("dropup", $(document).height() - $(this).offset().top <= 200);
            });

            $(document).on("click", ".view-screenshots", function () {
                var row = Table.api.getrowbyindex(table, parseInt($(this).data("index")));
                var data = [];
                $.each(row.screenshots, function (i, j) {
                    data.push({
                        "src": j
                    });
                });
                var json = {
                    "title": row.title,
                    "data": data
                };
                top.Layer.photos(top.JSON.parse(JSON.stringify({photos: json})));
            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        config: function () {
            //添加向发件人发送测试邮件按钮和方法
            $('input[name="row[mail_from]"]').parent().next().append('<a class="btn btn-info testmail">' + __('Send a test message') + '</a>');
            $(document).on("click", ".testmail", function () {
                var that = this;
                Layer.prompt({title: __('Please input your email'), formType: 0}, function (value, index) {
                    Backend.api.ajax({
                        url: "/api/ems/index/emailtest",
                        data: $(that).closest("form").serialize() + "&receiver=" + value
                    });
                });
            });

            //复制变量名称
            window['Clipboard']=clipboard;
            $(".btn-copy").mouseover(function(){
                if (this.clip !== undefined) {
                    this.clip.destroy();
                }
                var ids = $(this).attr("id");
                this.clip = new Clipboard('#'+ids);
                this.clip.on('success', function(e) {
                    Toastr.success(__('复制成功！'));
                    e.clearSelection();
                });
                this.clip.on('error', function(e) {
                    Toastr.error(__('复制失败！'));
                    e.clearSelection();
                });
            });

            Controller.api.bindevent();
        },
        api: {
            formatter: {
                title: function (value, row, index) {
                    var title = '<a class="title" href="' + Config.easyadmin.cloud_url + '/' + row.url + '" data-toggle="tooltip" title="' + __('View addon home page') + '" target="_blank">' + value + '</a>';
                    if (row.screenshots && row.screenshots.length > 0) {
                        title += ' <a href="javascript:;" data-index="' + index + '" class="view-screenshots text-success" title="' + __('View addon screenshots') + '" data-toggle="tooltip"><i class="fa fa-image"></i></a>';
                    }
                    return title;
                },
                operate: function (value, row, index) {
                    return Template("operatetpl", {item: row, index: index});
                },
                toggle: function (value, row, index) {
                    if (!row.addon) {
                        return '';
                    }
                    return '<a href="javascript:;" data-toggle="tooltip" title="' + __('Click to toggle status') + '" class="btn btn-toggle btn-' + (row.addon.state == 1 ? "disable" : "enable") + '" data-action="' + (row.addon.state == 1 ? "disable" : "enable") + '" data-name="' + row.name + '"><i class="fa ' + (row.addon.state == 0 ? 'fa-toggle-on fa-rotate-180 text-gray' : 'fa-toggle-on text-success') + ' fa-2x"></i></a>';
                },
                author: function (value, row, index) {
                    var url = 'javascript:';
                    if (typeof row.homepage !== 'undefined') {
                        url = row.homepage;
                    } else if (typeof row.qq !== 'undefined') {
                        url = 'https://wpa.qq.com/msgrd?v=3&uin=' + row.qq + '&site=easyadmin.vip&menu=yes';
                    }
                    return '<a href="' + url + '" target="_blank" data-toggle="tooltip" title="' + __('Click to contact developer') + '" class="text-primary">' + value + '</a>';
                },
                price: function (value, row, index) {
                    if (isNaN(value)) {
                        return value;
                    }
                    return parseFloat(value) == 0 ? '<span class="text-success">' + __('Free') + '</span>' : '<span class="text-danger">￥' + value + '</span>';
                },
                downloads: function (value, row, index) {
                    return value;
                },
                version: function (value, row, index) {
                    return row.addon && row.addon.version != row.version ? '<a href="' + row.url + '?version=' + row.version + '" target="_blank"><span class="releasetips text-primary" data-toggle="tooltip" title="' + __('New version tips', row.version) + '">' + row.addon.version + '<i></i></span></a>' : row.version;
                },
                home: function (value, row, index) {
                    return row.addon ? '<a href="/index/' + row.addon.name + '.html" data-toggle="tooltip" title="' + __('View addon index page') + '" target="_blank"><i class="fa fa-home text-primary"></i></a>' : '<a href="javascript:;"><i class="fa fa-home text-gray"></i></a>';
                },
                subnode: function (value, row, index) {
                    return '<a href="javascript:;" data-toggle="tooltip" title="' + __('Toggle sub menu') + '" data-id="' + row.id + '" data-pid="' + row.pid + '" class="btn btn-xs '
                        + (row.haschild == 1 || row.ismenu == 1 ? 'btn-success' : 'btn-default disabled') + ' btn-node-sub"><i class="fa fa-' + (row.haschild == 1 || row.ismenu == 1 ? 'sitemap' : 'list') + '"></i></a>';
                },
            },
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            userinfo: {
                get: function () {
                    var userinfo = localStorage.getItem("easyadmin_userinfo");
                    return userinfo ? JSON.parse(userinfo) : null;
                },
                set: function (data) {
                    if (data) {
                        localStorage.setItem("easyadmin_userinfo", JSON.stringify(data));
                    } else {
                        localStorage.removeItem("easyadmin_userinfo");
                    }
                }
            },
        }
    };
    return Controller;
});