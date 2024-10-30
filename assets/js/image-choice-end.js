jQuery(document).ready(function ($) {

    jQuery('#pcafe_imgp_color').wpColorPicker();

    var file_frame, attachment;

    function create_file_frame() {
        file_frame = wp.media.frames.file_frame = wp.media({
            title: jQuery(this).data("uploader_title"),
            button: {
                text: "Select Image",
            },
            multiple: false, // Set to true to allow multiple files to be selected
        });
    }

    $("#field_choices").on("click", ".pc_image_media_upload", function (event) {
        var self = $(this);

        event.preventDefault();
        if (!file_frame) {
            create_file_frame();
        }

        file_frame.open();

        file_frame.on("select", function (event) {
            attachment = file_frame.state().get("selection").first().toJSON();
            //console.log(attachment);
            self.parent()
                .siblings(".field-choice-image-url")
                .val(attachment.url)
                .trigger("propertychange");
            self.parent()
                .siblings(".field-choice-image-id")
                .val(attachment.id)
                .trigger("propertychange");
            SetFieldChoices();
            file_frame.off("select");
            self.parent().find(".image_preview_box").show();
            self.parent()
                .find(".img_pick_preview")
                .css("background-image", "url(" + attachment.url + ")");
            self.hide();

            var m = $("#choices-ui-flyout");
            m.length &&
                m.addClass(
                    "gform-flyout--anim-in-ready gform-flyout--anim-in-active"
                );
        });
    });

    $("#field_choices").on("click", ".remove_pick_img", function (event) {
        var self = $(this);

        self.parent()
            .parent()
            .parent()
            .find(".field-choice-image-url")
            .val("")
            .trigger("propertychange");

        self.parent()
            .parent()
            .parent()
            .find(".field-choice-image-id")
            .val("")
            .trigger("propertychange");

        self.parent().parent().find(".image_preview_box").hide();
        self.parent().parent().find(".pc_image_media_upload").show();
    });

    var imgg = $("#radio_choice_image_url_0").val();

    //console.log(imgg);

    if (imgg) {
        $(".img_pick_preview").css("background-image", "url(" + imgg + ")");
    }

    // var o = $("#field_settings_container");
    // console.log(o);

    function getChoice(i) {
        "undefined" == typeof i && (i = GetSelectedField());
    }

    gform.addAction("gform_load_field_choices", function () {
        var i = GetSelectedField();
        var o = '[class*="-choice-row"]:visible';
        var h = $("#field_choices");

        h.find(o).each(function () {
            var no = $(this),
                c = no.data("index");

            var s =
                i.choices.length && void 0 !== i.choices[c].imageUrl
                    ? i.choices[c].imageUrl
                    : "";

            if (s) {
                $(this).find(".image_preview_box").show();
                $(this).find(".pc_image_media_upload").hide();
            }

            no.find(".img_pick_preview").css(
                "background-image",
                "url(" + s + ")"
            );
        });
    });

    jQuery(document).on("gform_load_field_settings", function (event, field) {
        var i = field;
        var o = '[class*="-choice-row"]';
        var h = $("#field_choices");
        var im;
        if ($("#gfic_enable_imgchoice").is(":checked")) {
            im = true;
        } else {
            im = false;
        }

        $("#gfic_enable_imgchoice").on("click", function () {
            if ($(this).is(":checked")) {
                h.find(o).each(function () {
                    var no = $(this);
                    no.find(".show_hide_trigger").show();
                });
            } else {
                h.find(o).each(function () {
                    var no = $(this);
                    no.find(".show_hide_trigger").hide();
                });
            }
        });

        h.find(o).each(function () {
            var no = $(this),
                c = no.data("index");

            var s =
                i.choices.length && void 0 !== i.choices[c].imageUrl
                    ? i.choices[c].imageUrl
                    : "";
            if (s) {
                no.find(".image_preview_box").show();
                no.find(".pc_image_media_upload").hide();
            }

            if (im) {
                no.find(".show_hide_trigger").show();
            } else {
                no.find(".show_hide_trigger").hide();
            }

            no.find(".img_pick_preview").css(
                "background-image",
                "url(" + s + ")"
            );
        });
    });
});
