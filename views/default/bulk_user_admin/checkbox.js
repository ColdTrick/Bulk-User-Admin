define(function(require) {
    var elgg = require("elgg");
    var $ = require("jquery");

    $("#checkAll").click(function () {
        $('.elgg-form-bulk-user-admin-delete input:checkbox').not(this).prop('checked', this.checked);
    });
});
