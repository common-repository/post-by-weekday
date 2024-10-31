jQuery(document).ready(function (a) {
  a("#btn-pbwd").on("click", function (b) {
    b.preventDefault();
    let _el = a(this);
    let post_id = a("#post_ID").val();
    let selected = [];
    a("#listday input:checked").each(function () {
      selected.push(a(this).attr("value"));
    });
    a.ajax({
      type: "POST",
      dataType: "json",
      url: "/wp-admin/admin-ajax.php",
      data: {
        action: "setup_post_by_weekday",
        post_id: post_id,
        selected: selected.join(","),
      },
      success: function (c) {
        if (c.success) {
          _el.attr("disabled", "disabled");
          setTimeout(function () {
            _el.removeAttr("disabled");
          }, 1500);
        }
        window.location.reload();
      },
      error: function (c) {
        console.error(c);
      },
    });
  });
});
