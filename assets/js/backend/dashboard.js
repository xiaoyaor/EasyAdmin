define(['jquery', 'bootstrap','addtabs', 'backend','easy', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, undefined, Backend, Easy,Datatable, Table, Echarts, undefined, Template) {

    var Controller = {
        index: function () {
            // 插件配置
            $(document).on("click", ".btn-config", function () {
                var name = $(this).data("name");
                Easy.api.open("addon/config?name=" + name, __('Custom'));
            });

            // 显示配置
            $(document).on("click", "#custom-view", function () {
                Easy.api.open("dashboard/config?name=all", __('Custom'));
            });

            var firstnav = $("#dashboardnav .nav-addtabs");
            //一级菜单自适应
            $(window).resize(function () {
                firstnav.width(firstnav.parent().width());
                firstnav.refreshAddtabs();
            });
            $(window).resize();

        },
        config: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'addon/downloaded',
                    add_url: '',
                    edit_url: '',
                    del_url: ''
                }
            });

            var table = $("#table");
            var tableOptions = {
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                escape: false,
                pk: 'id',
                sortName: 'weigh',
                pagination: false,
                commonSearch: false,
                search: true,
                templateView: false,
                clickToSelect: false,
                showColumns: false,
                showToggle: false,
                showExport: false,
                showSearch: false,
                searchFormVisible: true,
                columns: [
                    [
                        {field: 'name', title: __('标识'), operate: false, visible: true, width: '120px'},
                        {field: 'title', title: __('名称'), operate: 'LIKE', align: 'left'},
                        {
                            field: 'dashboard',
                            title: __('控制台显示'),
                            align: 'center',
                            formatter: Controller.api.formatter.dashboard
                        },
                        {
                            field: 'tab',
                            title: __('标签页显示'),
                            align: 'center',
                            formatter: Controller.api.formatter.tab
                        },
                        {field: 'operate', title: __('Operate'),
                            buttons: [
                                {
                                    name: 'config',
                                    text: '配置',
                                    title: function (row) {
                                        return "["+row.title+"]插件配置";
                                    },
                                    icon: 'fa fa-cogs fa-fw',
                                    classname: 'btn btn-xs btn-info btn-dialog ',
                                    url: function (row) {
                                        return 'addon/config?name='+row.name;
                                    }
                                }

                            ],
                            table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            };

            var dashboard = function (name, action,value) {
                Easy.api.ajax({
                    url: 'dashboard/config/name/'+name,
                    data: {'row[name]': name, 'row[action]': action, 'row[value]': value}
                }, function (data, ret) {
                    Layer.closeAll();
                    $('.btn-refresh').trigger('click');
                }, function (data, ret) {
                    return false;
                });
            };

            // 点击启用/禁用
            $(document).on("click", ".btn-change", function () {
                var name = $(this).data("name");
                var action = $(this).data("action");
                var value = $(this).data("value");
                dashboard(name, action,value);
            });

            // 初始化表格
            table.bootstrapTable(tableOptions);

            // 为表格绑定事件
            Table.api.bindevent(table);
            table.on('load-success.bs.table',function(data){
                $(".btn-primary").data("area", ["80%","90%"]);
            });
        },
        api: {
            formatter: {
                dashboard: function (value, row, index) {
                    if (!row) {
                        return '';
                    }
                    return '<a href="javascript:;" data-toggle="tooltip" title="' + __('Click to toggle status') + '" class="btn btn-change' + '" data-value="' + (value) + '" data-name="' + row.name+ '" data-action="dashboard"><i class="fa ' + (value == 0 ? 'fa-toggle-on fa-rotate-180 text-gray' : 'fa-toggle-on text-success') + ' fa-2x"></i></a>';
                },
                tab: function (value, row, index) {
                    if (!row) {
                        return '';
                    }
                    return '<a href="javascript:;" data-toggle="tooltip" title="' + __('Click to toggle status') + '" class="btn btn-change' + '" data-value="' + (value) + '" data-name="' + row.name + '" data-action="tab"><i class="fa ' + (value == 0 ? 'fa-toggle-on fa-rotate-180 text-gray' : 'fa-toggle-on text-success') + ' fa-2x"></i></a>';
                },
            },
        }
    };

    return Controller;
});