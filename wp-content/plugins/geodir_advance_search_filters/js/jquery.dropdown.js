/*
 * jQuery dropdown: A simple dropdown plugin
 *
 * MODIFIED FOR GEODIRECTORY
 *
 * Copyright A Beautiful Site, LLC. (http://www.abeautifulsite.net/)
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 */
jQuery && function(t) {
    function o(o, d) {
        var n = o ? t(this) : d,
            a = t(n.attr("data-dropdown")),
            s = n.hasClass("dropdown-open");
        if (o) {
            if (t(o.target).hasClass("dropdown-ignore")) return;
            o.preventDefault(), o.stopPropagation()
        } else if (n !== d.target && t(d.target).hasClass("dropdown-ignore")) return;
        r(), s || n.hasClass("dropdown-disabled") || (n.addClass("dropdown-open"), a.data("dropdown-trigger", n).show(), e(), a.trigger("show", {
            dropdown: a,
            trigger: n
        }))
    }

    function r(o) {
        var r = o ? t(o.target).parents().addBack() : null;
        if (r && r.is("div.gd-dropdown")) {
            if (!r.is(".dropdown-menu")) return;
            if (!r.is("A")) return
        }
        t(document).find("div.gd-dropdown:visible").each(function() {
            var o = t(this);
            o.hide().removeData("dropdown-trigger").trigger("hide", {
                dropdown: o
            })
        }), t(document).find(".dropdown-open").removeClass("dropdown-open")
    }

    function e() {
        var o = t(".gd-dropdown:visible").eq(0),
            r = o.data("dropdown-trigger"),
            e = r ? parseInt(r.attr("data-horizontal-offset") || 0, 10) : null,
            d = r ? parseInt(r.attr("data-vertical-offset") || 0, 10) : null;
        0 !== o.length && r && o.css(o.hasClass("dropdown-relative") ? {
            left: o.hasClass("dropdown-anchor-right") ? r.position().left - (o.outerWidth(!0) - r.outerWidth(!0)) - parseInt(r.css("margin-right"), 10) + e : r.position().left + parseInt(r.css("margin-left"), 10) + e,
            top: r.position().top + r.outerHeight(!0) - parseInt(r.css("margin-top"), 10) + d
        } : {
            left: o.hasClass("dropdown-anchor-right") ? r.offset().left - (o.outerWidth() - r.outerWidth()) + e : r.offset().left + e,
            top: r.offset().top + r.outerHeight() + d
        })
    }
    t.extend(t.fn, {
        dropdown: function(e, d) {
            switch (e) {
                case "show":
                    return o(null, t(this)), t(this);
                case "hide":
                    return r(), t(this);
                case "attach":
                    return t(this).attr("data-dropdown", d);
                case "detach":
                    return r(), t(this).removeAttr("data-dropdown");
                case "disable":
                    return t(this).addClass("dropdown-disabled");
                case "enable":
                    return r(), t(this).removeClass("dropdown-disabled")
            }
        }
    }), t(document).on("click.dropdown", "[data-dropdown]", o), t(document).on("click.dropdown", r), t(window).on("resize", e)
}(jQuery);