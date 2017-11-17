jQuery(document).ready(function() {
    function p() {
        if (jQuery("#to_name").val() == "") {
            t.addClass("error");
            n.text(gt_cus.to_name_string);
            n.addClass("message_error2");
            return false
        } else {
            t.removeClass("error");
            n.text("");
            n.removeClass("message_error2");
            return true
        }
    }

    function d() {
        var e = 0;
        if (jQuery("#to_email").val() == "") {
            e = 1
        } else if (jQuery("#to_email").val() != "") {
            var t = jQuery("#to_email").val();
            var n = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if (n.test(t)) {
                e = 0
            } else {
                e = 1
            }
        }
        if (e) {
            r.addClass("error");
            i.text(gt_cus.email_string);
            i.addClass("message_error2");
            return false
        } else {
            r.removeClass("error");
            i.text("");
            i.removeClass("message_error");
            return true
        }
    }

    function v() {
        if (jQuery("#yourname").val() == "") {
            s.addClass("error");
            o.text(gt_cus.name_string);
            o.addClass("message_error2");
            return false
        } else {
            s.removeClass("error");
            o.text("");
            o.removeClass("message_error2");
            return true
        }
    }

    function m() {
        var e = 0;
        if (jQuery("#youremail").val() == "") {
            e = 1
        } else if (jQuery("#youremail").val() != "") {
            var t = jQuery("#youremail").val();
            var n = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if (n.test(t)) {
                e = 0
            } else {
                e = 1
            }
        }
        if (e) {
            u.addClass("error");
            a.text(gt_cus.email_string);
            a.addClass("message_error2");
            return false
        } else {
            u.removeClass("error");
            a.text("");
            a.removeClass("message_error");
            return true
        }
    }

    function g() {
        if (jQuery("#frnd_comments").val() == "") {
            f.addClass("error");
            l.text(gt_cus.comments_string);
            l.addClass("message_error2");
            return false
        } else {
            f.removeClass("error");
            l.text("");
            l.removeClass("message_error2");
            return true
        }
    }

    function y() {
        if (jQuery("#frnd_subject").val() == "") {
            c.addClass("error");
            h.text(gt_cus.subject_string);
            h.addClass("message_error2");
            return false
        } else {
            c.removeClass("error");
            h.text("");
            h.removeClass("message_error2");
            return true
        }
    }

    function C() {
        if (jQuery("#agt_mail_name").val() == "") {
            w.addClass("error");
            E.text(gt_cus.your_name_string);
            E.addClass("message_error2");
            return false
        } else {
            w.removeClass("error");
            E.text("");
            E.removeClass("message_error2");
            return true
        }
    }

    function k() {
        var e = 0;
        if (jQuery("#agt_mail_email").val() == "") {
            e = 1
        } else if (jQuery("#agt_mail_email").val() != "") {
            var t = jQuery("#agt_mail_email").val();
            var n = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if (n.test(t)) {
                e = 0
            } else {
                e = 1
            }
        }
        if (e) {
            S.addClass("error");
            x.text(gt_cus.email_string);
            x.addClass("message_error2");
            return false
        } else {
            S.removeClass("error");
            x.text("");
            x.removeClass("message_error");
            return true
        }
    }

    function L() {
        if (jQuery("#agt_mail_msg").val() == "") {
            T.addClass("error");
            N.text(gt_cus.comments_string);
            N.addClass("message_error2");
            return false
        } else {
            T.removeClass("error");
            N.text("");
            N.removeClass("message_error2");
            return true
        }
    }

    function A() {
        document.getElementById("agt_mail_name").value = "";
        document.getElementById("agt_mail_email").value = "";
        document.getElementById("agt_mail_phone").value = "";
        document.getElementById("agt_mail_msg").value = ""
    }

    function I() {
        if (jQuery("#full_name").val() == "") {
            M.addClass("error");
            _.text(gt_cus.full_name_string);
            _.addClass("message_error2");
            return false
        } else {
            M.removeClass("error");
            _.text("");
            _.removeClass("message_error2");
            return true
        }
    }

    function q() {
        if (jQuery("#user_number").val() == "") {
            D.addClass("error");
            P.text(gt_cus.contact_no_string);
            P.addClass("message_error2");
            return false
        } else {
            D.removeClass("error");
            P.text("");
            P.removeClass("message_error2");
            return true
        }
    }

    function R() {
        if (jQuery("#user_position").val() == "") {
            H.addClass("error");
            B.text(gt_cus.pos_bus_string);
            B.addClass("message_error2");
            return false
        } else {
            H.removeClass("error");
            B.text("");
            B.removeClass("message_error2");
            return true
        }
    }

    function U() {
        if (jQuery("#user_comments").val() == "") {
            j.addClass("error");
            F.text(gt_cus.comments_string);
            F.addClass("message_error2");
            return false
        } else {
            j.removeClass("error");
            F.text("");
            F.removeClass("message_error2");
            return true
        }
    }
    jQuery(".toggle").show();
    jQuery("#trigger").click(function() {
        jQuery(this).toggleClass("active").next().slideToggle("slow");
        if (jQuery("#trigger").hasClass("triggeroff")) {
            jQuery("#trigger").removeClass("triggeroff");
            jQuery("#trigger").addClass("triggeron")
        } else {
            jQuery("#trigger").removeClass("triggeron");
            jQuery("#trigger").addClass("triggeroff")
        }
    });
    jQuery("ul#sitemap").simpletreeview({
        open: '<span class="simpletreeviewopen">&darr;</span>',
        close: '<span class="simpletreeviewclose">&rarr;</span>',
        slide: true,
        speed: "slow",
        collapsed: true,
        expand: "0.0"
    });
    jQuery(".togglecats").hide();
    jQuery(".form_cat :checkbox").click(function() {
        var e = "#togglecats" + this.id;
        if (this.checked) jQuery(e).show();
        else {
            jQuery(e).hide();
            jQuery(e + " :checkbox").each(function() {
                if (this.checked) {
                    jQuery(this).attr({
                        checked: false
                    })
                }
            })
        }
    });
    jQuery(".togglecats").hide();
    jQuery(".togglecats").hide();
    jQuery(".togglecats :checkbox").each(function() {
        if (this.checked) {
            jQuery(this).parents(".togglecats").show()
        }
    });
    jQuery(".form_cat :radio").click(function() {
        var e = "#togglecats" + this.value;
        if (this.radio) jQuery(e).show();
        else jQuery(e).hide()
    });
    if (typeof cal_go == "function") {
        cal_go()
    }
    jQuery(document).ready(function() {
        function h() {
            if (jQuery("#user_login").val() == "") {
                t.addClass("error");
                n.text(gt_cus.username_string);
                n.addClass("message_error2");
                return false
            } else {
                t.removeClass("error");
                n.text("");
                n.removeClass("message_error2");
                return true
            }
        }

        function p() {
            if (jQuery("#user_pass").val() == "") {
                r.addClass("error");
                i.text(gt_cus.pass_string);
                i.addClass("message_error2");
                return false
            } else {
                r.removeClass("error");
                i.text("");
                i.removeClass("message_error2");
                return true
            }
        }

        function d() {
            if (jQuery("#user_login1reg").val() == "") {
                o.addClass("error");
                u.text(gt_cus.username_string);
                u.addClass("message_error2");
                return false
            } else {
                o.removeClass("error");
                u.text("");
                u.removeClass("message_error2");
                return true
            }
        }

        function v() {
            if (jQuery("#user_fname").val() == "") {
                l.addClass("error");
                c.text(gt_cus.name_string);
                c.addClass("message_error2");
                return false
            } else {
                l.removeClass("error");
                c.text("");
                c.removeClass("message_error2");
                return true
            }
        }

        function m() {
            var e = 0;
            if (jQuery("#user_email").val() == "") {
                e = 1
            } else if (jQuery("#user_email").val() != "") {
                var t = jQuery("#user_email").val();
                var n = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
                if (n.test(t)) {
                    e = 0
                } else {
                    e = 1
                }
            }
            if (e) {
                a.addClass("error");
                f.text(gt_cus.email_string);
                f.addClass("message_error2");
                return false
            } else {
                a.removeClass("error");
                f.text("");
                f.removeClass("message_error");
                return true
            }
        }

        function m() {
            var e = 0;
            if (jQuery("#user_email").val() == "") {
                e = 1
            } else if (jQuery("#user_email").val() != "") {
                var t = jQuery("#user_email").val();
                var n = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                if (n.test(t)) {
                    e = 0
                } else {
                    e = 1
                }
            }
            if (e) {
                a.addClass("error");
                f.text(gt_cus.email_string);
                f.addClass("message_error2");
                return false
            } else {
                a.removeClass("error");
                f.text("");
                f.removeClass("message_error");
                return true
            }
        }

        function v() {
            if (jQuery("#user_fname").val() == "") {
                l.addClass("error");
                c.text(gt_cus.name_string);
                c.addClass("message_error2");
                return false
            } else {
                l.removeClass("error");
                c.text("");
                c.removeClass("message_error2");
                return true
            }
        }

        function S() {
            if (jQuery("#pwd").val() != "" & jQuery("#cpwd").val() == "") {
                w.addClass("error");
                E.text(gt_cus.cpass_string);
                E.addClass("message_error2");
                return false
            } else if (jQuery("#pwd").val() != jQuery("#cpwd").val()) {
                w.addClass("error");
                E.text(gt_cus.err_cpass_string);
                E.addClass("message_error2");
                return false
            } else {
                w.removeClass("error");
                E.text("");
                E.removeClass("message_error2");
                return true
            }
        }
        var e = jQuery("#cus_loginform");
        var t = jQuery("#user_login");
        var n = jQuery("#user_loginInfo");
        var r = jQuery("#user_pass");
        var i = jQuery("#user_passInfo");
        e.submit(function() {
            if (h() & p()) return true;
            else return false
        });
        var s = jQuery("#cus_registerform");
        var o = jQuery("#user_login1reg");
        var u = jQuery("#user_login1regInfo");
        var a = jQuery("#user_email");
        var f = jQuery("#user_emailInfo");
        var l = jQuery("#user_fname");
        var c = jQuery("#user_fnameInfo");
        s.submit(function() {
            if (d() & m() & v()) return true;
            else return false
        });
        var e = jQuery("#cus_loginform");
        var t = jQuery("#user_login");
        var n = jQuery("#user_loginInfo");
        var r = jQuery("#user_pass");
        var i = jQuery("#user_passInfo");
        e.submit(function() {
            if (h() & p()) return true;
            else return false
        });
        var g = jQuery("#profileform");
        var a = jQuery("#user_email");
        var f = jQuery("#user_emailInfo");
        var l = jQuery("#user_fname");
        var c = jQuery("#user_fnameInfo");
        var y = jQuery("#pwd");
        var b = jQuery("#pwdInfo");
        var w = jQuery("#cpwd");
        var E = jQuery("#cpwdInfo");
        g.submit(function() {
            if (m() & v() & S()) return true;
            else return false
        })
    });
    jQuery("a.b_sendtofriend").click(function(e) {
        e.preventDefault();
        jQuery("#basic-modal-content").modal({
            persist: true
        })
    });
    jQuery("a.b_claim_listing").click(function(e) {
        e.preventDefault();
        jQuery("#basic-modal-content4").modal({
            persist: true
        })
    });
    jQuery("a.b_send_inquiry").click(function(e) {
        e.preventDefault();
        jQuery("#basic-modal-content2").modal({
            persist: true
        })
    });
    jQuery("p.links a.a_image_sort").click(function(e) {
        e.preventDefault();
        jQuery("#basic-modal-content3").modal({
            persist: true
        })
    });
    var e = jQuery("#send_to_frnd");
    var t = jQuery("#to_name");
    var n = jQuery("#to_nameInfo");
    var r = jQuery("#to_email");
    var i = jQuery("#to_emailInfo");
    var s = jQuery("#yourname");
    var o = jQuery("#yournameInfo");
    var u = jQuery("#youremail");
    var a = jQuery("#youremailInfo");
    var f = jQuery("#frnd_comments");
    var l = jQuery("#frnd_commentsInfo");
    var c = jQuery("#frnd_subject");
    var h = jQuery("#frnd_subjectInfo");
    t.blur(p);
    r.blur(d);
    s.blur(v);
    u.blur(m);
    f.blur(g);
    c.blur(y);
    t.keyup(p);
    r.keyup(d);
    s.keyup(v);
    u.keyup(m);
    f.keyup(g);
    c.keyup(y);
    e.submit(function() {
        if (p() & d() & v() & m() & y() & g()) {
            function e() {
                document.getElementById("to_name").value = "";
                document.getElementById("to_email").value = "";
                document.getElementById("yourname").value = "";
                document.getElementById("youremail").value = "";
                document.getElementById("frnd_subject").value = "";
                document.getElementById("frnd_comments").value = ""
            }
            return true
        } else {
            return false
        }
    });
    var b = jQuery("#agt_mail_agent");
    var w = jQuery("#agt_mail_name");
    var E = jQuery("#span_agt_mail_name");
    var S = jQuery("#agt_mail_email");
    var x = jQuery("#span_agt_mail_email");
    var T = jQuery("#agt_mail_msg");
    var N = jQuery("#span_agt_mail_msg");
    w.blur(C);
    S.blur(k);
    T.blur(L);
    w.keyup(C);
    S.keyup(k);
    T.keyup(L);
    b.submit(function() {
        if (C() & k() & L()) {
            return true
        } else {
            return false
        }
    });
    var O = jQuery("#claim_form");
    var M = jQuery("#full_name");
    var _ = jQuery("#full_nameInfo");
    var D = jQuery("#user_number");
    var P = jQuery("#user_numberInfo");
    var H = jQuery("#user_position");
    var B = jQuery("#user_positionInfo");
    var j = jQuery("#user_comments");
    var F = jQuery("#user_commentsInfo");
    M.blur(I);
    D.blur(q);
    H.blur(R);
    j.blur(U);
    M.keyup(I);
    D.keyup(q);
    H.keyup(R);
    j.keyup(U);
    O.submit(function() {
        if (I() & R() & U()) {
            function e() {
                document.getElementById("full_name").value = "";
                document.getElementById("user_number").value = "";
                document.getElementById("user_position").value = "";
                document.getElementById("user_comments").value = ""
            }
            return true
        } else {
            return false
        }
    })
});
(function(e) {
    var t = function(t, n) {
        var r = e.extend({}, e.fn.nivoSlider.defaults, n);
        var i = {
            currentSlide: 0,
            currentImage: "",
            totalSlides: 0,
            running: false,
            paused: false,
            stop: false,
            controlNavEl: false
        };
        var s = e(t);
        s.data("nivo:vars", i).addClass("nivoSlider");
        var o = s.children();
        o.each(function() {
            var t = e(this);
            var n = "";
            if (!t.is("img")) {
                if (t.is("a")) {
                    t.addClass("nivo-imageLink");
                    n = t
                }
                t = t.find("img:first")
            }
            var r = r === 0 ? t.attr("width") : t.width(),
                s = s === 0 ? t.attr("height") : t.height();
            if (n !== "") {
                n.css("display", "none")
            }
            t.css("display", "none");
            i.totalSlides++
        });
        if (r.randomStart) {
            r.startSlide = Math.floor(Math.random() * i.totalSlides)
        }
        if (r.startSlide > 0) {
            if (r.startSlide >= i.totalSlides) {
                r.startSlide = i.totalSlides - 1
            }
            i.currentSlide = r.startSlide
        }
        if (e(o[i.currentSlide]).is("img")) {
            i.currentImage = e(o[i.currentSlide])
        } else {
            i.currentImage = e(o[i.currentSlide]).find("img:first")
        }
        if (e(o[i.currentSlide]).is("a")) {
            e(o[i.currentSlide]).css("display", "block")
        }
        var u = e('<img class="nivo-main-image" src="#" />');
        u.attr("src", i.currentImage.attr("src")).show();
        s.append(u);
        e(window).resize(function() {
            s.children("img").width(s.width());
            u.attr("src", i.currentImage.attr("src"));
            u.stop().height("auto");
            e(".nivo-slice").remove();
            e(".nivo-box").remove()
        });
        s.append(e('<div class="nivo-caption"></div>'));
        var f = function(t) {
            var n = e(".nivo-caption", s);
            if (i.currentImage.attr("title") != "" && i.currentImage.attr("title") != undefined) {
                var r = i.currentImage.attr("title");
                if (r.substr(0, 1) == "#") r = e(r).html();
                if (n.css("display") == "block") {
                    setTimeout(function() {
                        n.html(r)
                    }, t.animSpeed)
                } else {
                    n.html(r);
                    n.stop().fadeIn(t.animSpeed)
                }
            } else {
                n.stop().fadeOut(t.animSpeed)
            }
        };
        f(r);
        var l = 0;
        if (!r.manualAdvance && o.length > 1) {
            l = setInterval(function() {
                v(s, o, r, false)
            }, r.pauseTime)
        }
        if (r.directionNav) {
            s.append('<div class="nivo-directionNav"><a class="nivo-prevNav">' + r.prevText + '</a><a class="nivo-nextNav">' + r.nextText + "</a></div>");
            e("a.nivo-prevNav", s).live("click", function() {
                if (i.running) {
                    return false
                }
                clearInterval(l);
                l = "";
                i.currentSlide -= 2;
                v(s, o, r, "prev")
            });
            e("a.nivo-nextNav", s).live("click", function() {
                if (i.running) {
                    return false
                }
                clearInterval(l);
                l = "";
                v(s, o, r, "next")
            })
        }
        if (r.controlNav) {
            i.controlNavEl = e('<div class="nivo-controlNav"></div>');
            s.after(i.controlNavEl);
            for (var c = 0; c < o.length; c++) {
                if (r.controlNavThumbs) {
                    i.controlNavEl.addClass("nivo-thumbs-enabled");
                    var h = o.eq(c);
                    if (!h.is("img")) {
                        h = h.find("img:first")
                    }
                    if (h.attr("data-thumb")) i.controlNavEl.append('<a class="nivo-control" rel="' + c + '"><img src="' + h.attr("data-thumb") + '" alt="" /></a>')
                } else {
                    i.controlNavEl.append('<a class="nivo-control" rel="' + c + '">' + (c + 1) + "</a>")
                }
            }
            e("a:eq(" + i.currentSlide + ")", i.controlNavEl).addClass("active");
            e("a", i.controlNavEl).bind("click", function() {
                if (i.running) return false;
                if (e(this).hasClass("active")) return false;
                clearInterval(l);
                l = "";
                u.attr("src", i.currentImage.attr("src"));
                i.currentSlide = e(this).attr("rel") - 1;
                v(s, o, r, "control")
            })
        }
        if (r.pauseOnHover) {
            s.hover(function() {
                i.paused = true;
                clearInterval(l);
                l = ""
            }, function() {
                i.paused = false;
                if (l === "" && !r.manualAdvance) {
                    l = setInterval(function() {
                        v(s, o, r, false)
                    }, r.pauseTime)
                }
            })
        }
        s.bind("nivo:animFinished", function() {
            u.attr("src", i.currentImage.attr("src"));
            i.running = false;
            e(o).each(function() {
                if (e(this).is("a")) {
                    e(this).css("display", "none")
                }
            });
            if (e(o[i.currentSlide]).is("a")) {
                e(o[i.currentSlide]).css("display", "block")
            }
            if (l === "" && !i.paused && !r.manualAdvance) {
                l = setInterval(function() {
                    v(s, o, r, false)
                }, r.pauseTime)
            }
            r.afterChange.call(this)
        });
        var p = function(t, n, r) {
            if (e(r.currentImage).parent().is("a")) e(r.currentImage).parent().css("display", "block");
            e('img[src="' + r.currentImage.attr("src") + '"]', t).not(".nivo-main-image,.nivo-control img").width(t.width()).css("visibility", "hidden").show();
            var i = e('img[src="' + r.currentImage.attr("src") + '"]', t).not(".nivo-main-image,.nivo-control img").parent().is("a") ? e('img[src="' + r.currentImage.attr("src") + '"]', t).not(".nivo-main-image,.nivo-control img").parent().height() : e('img[src="' + r.currentImage.attr("src") + '"]', t).not(".nivo-main-image,.nivo-control img").height();
            for (var s = 0; s < n.slices; s++) {
                var o = Math.round(t.width() / n.slices);
                if (s === n.slices - 1) {
                    t.append(e('<div class="nivo-slice" name="' + s + '"><img src="' + r.currentImage.attr("src") + '" style="position:absolute; width:' + t.width() + "px; height:auto; display:block !important; top:0; left:-" + (o + s * o - o) + 'px;" /></div>').css({
                        left: o * s + "px",
                        width: t.width() - o * s + "px",
                        height: i + "px",
                        opacity: "0",
                        overflow: "hidden"
                    }))
                } else {
                    t.append(e('<div class="nivo-slice" name="' + s + '"><img src="' + r.currentImage.attr("src") + '" style="position:absolute; width:' + t.width() + "px; height:auto; display:block !important; top:0; left:-" + (o + s * o - o) + 'px;" /></div>').css({
                        left: o * s + "px",
                        width: o + "px",
                        height: i + "px",
                        opacity: "0",
                        overflow: "hidden"
                    }))
                }
            }
            e(".nivo-slice", t).height(i);
            u.stop().animate({
                height: e(r.currentImage).height()
            }, n.animSpeed)
        };
        var d = function(t, n, r) {
            if (e(r.currentImage).parent().is("a")) e(r.currentImage).parent().css("display", "block");
            e('img[src="' + r.currentImage.attr("src") + '"]', t).not(".nivo-main-image,.nivo-control img").width(t.width()).css("visibility", "hidden").show();
            var i = Math.round(t.width() / n.boxCols),
                s = Math.round(e('img[src="' + r.currentImage.attr("src") + '"]', t).not(".nivo-main-image,.nivo-control img").height() / n.boxRows);
            for (var o = 0; o < n.boxRows; o++) {
                for (var f = 0; f < n.boxCols; f++) {
                    if (f === n.boxCols - 1) {
                        t.append(e('<div class="nivo-box" name="' + f + '" rel="' + o + '"><img src="' + r.currentImage.attr("src") + '" style="position:absolute; width:' + t.width() + "px; height:auto; display:block; top:-" + s * o + "px; left:-" + i * f + 'px;" /></div>').css({
                            opacity: 0,
                            left: i * f + "px",
                            top: s * o + "px",
                            width: t.width() - i * f + "px"
                        }));
                        e('.nivo-box[name="' + f + '"]', t).height(e('.nivo-box[name="' + f + '"] img', t).height() + "px")
                    } else {
                        t.append(e('<div class="nivo-box" name="' + f + '" rel="' + o + '"><img src="' + r.currentImage.attr("src") + '" style="position:absolute; width:' + t.width() + "px; height:auto; display:block; top:-" + s * o + "px; left:-" + i * f + 'px;" /></div>').css({
                            opacity: 0,
                            left: i * f + "px",
                            top: s * o + "px",
                            width: i + "px"
                        }));
                        e('.nivo-box[name="' + f + '"]', t).height(e('.nivo-box[name="' + f + '"] img', t).height() + "px")
                    }
                }
            }
            u.stop().animate({
                height: e(r.currentImage).height()
            }, n.animSpeed)
        };
        var v = function(t, n, r, i) {
            var s = t.data("nivo:vars");
            if (s && s.currentSlide === s.totalSlides - 1) {
                r.lastSlide.call(this)
            }
            if ((!s || s.stop) && !i) {
                return false
            }
            r.beforeChange.call(this);
            if (!i) {
                u.attr("src", s.currentImage.attr("src"))
            } else {
                if (i === "prev") {
                    u.attr("src", s.currentImage.attr("src"))
                }
                if (i === "next") {
                    u.attr("src", s.currentImage.attr("src"))
                }
            }
            s.currentSlide++;
            if (s.currentSlide === s.totalSlides) {
                s.currentSlide = 0;
                r.slideshowEnd.call(this)
            }
            if (s.currentSlide < 0) {
                s.currentSlide = s.totalSlides - 1
            }
            if (e(n[s.currentSlide]).is("img")) {
                s.currentImage = e(n[s.currentSlide])
            } else {
                s.currentImage = e(n[s.currentSlide]).find("img:first")
            }
            if (r.controlNav) {
                e("a", s.controlNavEl).removeClass("active");
                e("a:eq(" + s.currentSlide + ")", s.controlNavEl).addClass("active")
            }
            f(r);
            e(".nivo-slice", t).remove();
            e(".nivo-box", t).remove();
            var o = r.effect,
                l = "";
            if (r.effect === "random") {
                l = new Array("sliceDownRight", "sliceDownLeft", "sliceUpRight", "sliceUpLeft", "sliceUpDown", "sliceUpDownLeft", "fold", "fade", "boxRandom", "boxRain", "boxRainReverse", "boxRainGrow", "boxRainGrowReverse");
                o = l[Math.floor(Math.random() * (l.length + 1))];
                if (o === undefined) {
                    o = "fade"
                }
            }
            if (r.effect.indexOf(",") !== -1) {
                l = r.effect.split(",");
                o = l[Math.floor(Math.random() * l.length)];
                if (o === undefined) {
                    o = "fade"
                }
            }
            if (s.currentImage.attr("data-transition")) {
                o = s.currentImage.attr("data-transition")
            }
            s.running = true;
            var c = 0,
                h = 0,
                v = "",
                g = "",
                y = "",
                b = "";
            if (o === "sliceDown" || o === "sliceDownRight" || o === "sliceDownLeft") {
                p(t, r, s);
                c = 0;
                h = 0;
                v = e(".nivo-slice", t);
                if (o === "sliceDownLeft") {
                    v = e(".nivo-slice", t)._reverse()
                }
                v.each(function() {
                    var n = e(this);
                    n.css({
                        top: "0px"
                    });
                    if (h === r.slices - 1) {
                        setTimeout(function() {
                            n.animate({
                                opacity: "1.0"
                            }, r.animSpeed, "", function() {
                                t.trigger("nivo:animFinished")
                            })
                        }, 100 + c)
                    } else {
                        setTimeout(function() {
                            n.animate({
                                opacity: "1.0"
                            }, r.animSpeed)
                        }, 100 + c)
                    }
                    c += 50;
                    h++
                })
            } else if (o === "sliceUp" || o === "sliceUpRight" || o === "sliceUpLeft") {
                p(t, r, s);
                c = 0;
                h = 0;
                v = e(".nivo-slice", t);
                if (o === "sliceUpLeft") {
                    v = e(".nivo-slice", t)._reverse()
                }
                v.each(function() {
                    var n = e(this);
                    n.css({
                        bottom: "0px"
                    });
                    if (h === r.slices - 1) {
                        setTimeout(function() {
                            n.animate({
                                opacity: "1.0"
                            }, r.animSpeed, "", function() {
                                t.trigger("nivo:animFinished")
                            })
                        }, 100 + c)
                    } else {
                        setTimeout(function() {
                            n.animate({
                                opacity: "1.0"
                            }, r.animSpeed)
                        }, 100 + c)
                    }
                    c += 50;
                    h++
                })
            } else if (o === "sliceUpDown" || o === "sliceUpDownRight" || o === "sliceUpDownLeft") {
                p(t, r, s);
                c = 0;
                h = 0;
                var w = 0;
                v = e(".nivo-slice", t);
                if (o === "sliceUpDownLeft") {
                    v = e(".nivo-slice", t)._reverse()
                }
                v.each(function() {
                    var n = e(this);
                    if (h === 0) {
                        n.css("top", "0px");
                        h++
                    } else {
                        n.css("bottom", "0px");
                        h = 0
                    }
                    if (w === r.slices - 1) {
                        setTimeout(function() {
                            n.animate({
                                opacity: "1.0"
                            }, r.animSpeed, "", function() {
                                t.trigger("nivo:animFinished")
                            })
                        }, 100 + c)
                    } else {
                        setTimeout(function() {
                            n.animate({
                                opacity: "1.0"
                            }, r.animSpeed)
                        }, 100 + c)
                    }
                    c += 50;
                    w++
                })
            } else if (o === "fold") {
                p(t, r, s);
                c = 0;
                h = 0;
                e(".nivo-slice", t).each(function() {
                    var n = e(this);
                    var i = n.width();
                    n.css({
                        top: "0px",
                        width: "0px"
                    });
                    if (h === r.slices - 1) {
                        setTimeout(function() {
                            n.animate({
                                width: i,
                                opacity: "1.0"
                            }, r.animSpeed, "", function() {
                                t.trigger("nivo:animFinished")
                            })
                        }, 100 + c)
                    } else {
                        setTimeout(function() {
                            n.animate({
                                width: i,
                                opacity: "1.0"
                            }, r.animSpeed)
                        }, 100 + c)
                    }
                    c += 50;
                    h++
                })
            } else if (o === "fade") {
                p(t, r, s);
                g = e(".nivo-slice:first", t);
                g.css({
                    width: t.width() + "px"
                });
                g.animate({
                    opacity: "1.0"
                }, r.animSpeed * 2, "", function() {
                    t.trigger("nivo:animFinished")
                })
            } else if (o === "slideInRight") {
                p(t, r, s);
                g = e(".nivo-slice:first", t);
                g.css({
                    width: "0px",
                    opacity: "1"
                });
                g.animate({
                    width: t.width() + "px"
                }, r.animSpeed * 2, "", function() {
                    t.trigger("nivo:animFinished")
                })
            } else if (o === "slideInLeft") {
                p(t, r, s);
                g = e(".nivo-slice:first", t);
                g.css({
                    width: "0px",
                    opacity: "1",
                    left: "",
                    right: "0px"
                });
                g.animate({
                    width: t.width() + "px"
                }, r.animSpeed * 2, "", function() {
                    g.css({
                        left: "0px",
                        right: ""
                    });
                    t.trigger("nivo:animFinished")
                })
            } else if (o === "boxRandom") {
                d(t, r, s);
                y = r.boxCols * r.boxRows;
                h = 0;
                c = 0;
                b = m(e(".nivo-box", t));
                b.each(function() {
                    var n = e(this);
                    if (h === y - 1) {
                        setTimeout(function() {
                            n.animate({
                                opacity: "1"
                            }, r.animSpeed, "", function() {
                                t.trigger("nivo:animFinished")
                            })
                        }, 100 + c)
                    } else {
                        setTimeout(function() {
                            n.animate({
                                opacity: "1"
                            }, r.animSpeed)
                        }, 100 + c)
                    }
                    c += 20;
                    h++
                })
            } else if (o === "boxRain" || o === "boxRainReverse" || o === "boxRainGrow" || o === "boxRainGrowReverse") {
                d(t, r, s);
                y = r.boxCols * r.boxRows;
                h = 0;
                c = 0;
                var E = 0;
                var S = 0;
                var x = [];
                x[E] = [];
                b = e(".nivo-box", t);
                if (o === "boxRainReverse" || o === "boxRainGrowReverse") {
                    b = e(".nivo-box", t)._reverse()
                }
                b.each(function() {
                    x[E][S] = e(this);
                    S++;
                    if (S === r.boxCols) {
                        E++;
                        S = 0;
                        x[E] = []
                    }
                });
                for (var T = 0; T < r.boxCols * 2; T++) {
                    var N = T;
                    for (var C = 0; C < r.boxRows; C++) {
                        if (N >= 0 && N < r.boxCols) {
                            (function(n, i, s, u, f) {
                                var l = e(x[n][i]);
                                var c = l.width();
                                var h = l.height();
                                if (o === "boxRainGrow" || o === "boxRainGrowReverse") {
                                    l.width(0).height(0)
                                }
                                if (u === f - 1) {
                                    setTimeout(function() {
                                        l.animate({
                                            opacity: "1",
                                            width: c,
                                            height: h
                                        }, r.animSpeed / 1.3, "", function() {
                                            t.trigger("nivo:animFinished")
                                        })
                                    }, 100 + s)
                                } else {
                                    setTimeout(function() {
                                        l.animate({
                                            opacity: "1",
                                            width: c,
                                            height: h
                                        }, r.animSpeed / 1.3)
                                    }, 100 + s)
                                }
                            })(C, N, c, h, y);
                            h++
                        }
                        N--
                    }
                    c += 100
                }
            }
        };
        var m = function(e) {
            for (var t, n, r = e.length; r; t = parseInt(Math.random() * r, 10), n = e[--r], e[r] = e[t], e[t] = n);
            return e
        };
        var g = function(e) {
            if (this.console && typeof console.log !== "undefined") {
                console.log(e)
            }
        };
        this.stop = function() {
            if (!e(t).data("nivo:vars").stop) {
                e(t).data("nivo:vars").stop = true;
                g("Stop Slider")
            }
        };
        this.start = function() {
            if (e(t).data("nivo:vars").stop) {
                e(t).data("nivo:vars").stop = false;
                g("Start Slider")
            }
        };
        r.afterLoad.call(this);
        return this
    };
    e.fn.nivoSlider = function(n) {
        return this.each(function(r, i) {
            var s = e(this);
            if (s.data("nivoslider")) {
                return s.data("nivoslider")
            }
            var o = new t(this, n);
            s.data("nivoslider", o)
        })
    };
    e.fn.nivoSlider.defaults = {
        effect: "random",
        slices: 15,
        boxCols: 8,
        boxRows: 4,
        animSpeed: 500,
        pauseTime: 3e3,
        startSlide: 0,
        directionNav: true,
        controlNav: true,
        controlNavThumbs: false,
        pauseOnHover: true,
        manualAdvance: false,
        prevText: "Prev",
        nextText: "Next",
        randomStart: false,
        beforeChange: function() {},
        afterChange: function() {},
        slideshowEnd: function() {},
        lastSlide: function() {},
        afterLoad: function() {}
    };
    e.fn._reverse = [].reverse
})(jQuery);
(function(e) {
    e.flexslider = function(t, n) {
        var r = e(t);
        r.vars = e.extend({}, e.flexslider.defaults, n);
        var i = r.vars.namespace,
            s = ("ontouchstart" in window || window.navigator.msPointerEnabled || window.DocumentTouch && document instanceof DocumentTouch) && r.vars.touch,
            o = "click touchend MSPointerUp",
            u = "",
            a, f = r.vars.direction === "vertical",
            l = r.vars.reverse,
            c = r.vars.itemWidth > 0,
            h = r.vars.animation === "fade",
            p = r.vars.asNavFor !== "",
            d = {};
        focused = true;
        e.data(t, "flexslider", r);
        d = {
            init: function() {
                r.animating = false;
                r.currentSlide = r.vars.startAt;
                r.animatingTo = r.currentSlide;
                r.atEnd = r.currentSlide === 0 || r.currentSlide === r.last;
                r.containerSelector = r.vars.selector.substr(0, r.vars.selector.search(" "));
                r.slides = e(r.vars.selector, r);
                r.container = e(r.containerSelector, r);
                r.count = r.slides.length;
                r.syncExists = e(r.vars.sync).length > 0;
                if (r.vars.animation === "slide") r.vars.animation = "swing";
                r.prop = f ? "top" : "marginLeft";
                r.args = {};
                r.manualPause = false;
                r.stopped = false;
                r.transitions = !r.vars.video && !h && r.vars.useCSS && function() {
                    var e = document.createElement("div"),
                        t = ["perspectiveProperty", "WebkitPerspective", "MozPerspective", "OPerspective", "msPerspective"];
                    for (var n in t) {
                        if (e.style[t[n]] !== undefined) {
                            r.pfx = t[n].replace("Perspective", "").toLowerCase();
                            r.prop = "-" + r.pfx + "-transform";
                            return true
                        }
                    }
                    return false
                }();
                if (r.vars.controlsContainer !== "") r.controlsContainer = e(r.vars.controlsContainer).length > 0 && e(r.vars.controlsContainer);
                if (r.vars.manualControls !== "") r.manualControls = e(r.vars.manualControls).length > 0 && e(r.vars.manualControls);
                if (r.vars.randomize) {
                    r.slides.sort(function() {
                        return Math.round(Math.random()) - .5
                    });
                    r.container.empty().append(r.slides)
                }
                r.doMath();
                if (p) d.asNav.setup();
                r.setup("init");
                if (r.vars.controlNav) d.controlNav.setup();
                if (r.vars.directionNav) d.directionNav.setup();
                if (r.vars.keyboard && (e(r.containerSelector).length === 1 || r.vars.multipleKeyboard)) {
                    e(document).bind("keyup", function(e) {
                        var t = e.keyCode;
                        if (!r.animating && (t === 39 || t === 37)) {
                            var n = t === 39 ? r.getTarget("next") : t === 37 ? r.getTarget("prev") : false;
                            r.flexAnimate(n, r.vars.pauseOnAction)
                        }
                    })
                }
                if (r.vars.mousewheel) {
                    r.bind("mousewheel", function(e, t, n, i) {
                        e.preventDefault();
                        var s = t < 0 ? r.getTarget("next") : r.getTarget("prev");
                        r.flexAnimate(s, r.vars.pauseOnAction)
                    })
                }
                if (r.vars.pausePlay) d.pausePlay.setup();
                if (r.vars.slideshow) {
                    if (r.vars.pauseOnHover) {
                        r.hover(function() {
                            if (!r.manualPlay && !r.manualPause) r.pause()
                        }, function() {
                            if (!r.manualPause && !r.manualPlay && !r.stopped) r.play()
                        })
                    }
                    r.vars.initDelay > 0 ? setTimeout(r.play, r.vars.initDelay) : r.play()
                }
                if (s && r.vars.touch) d.touch();
                if (!h || h && r.vars.smoothHeight) e(window).bind("resize orientationchange focus", d.resize);
                setTimeout(function() {
                    r.vars.start(r)
                }, 200)
            },
            asNav: {
                setup: function() {
                    r.asNav = true;
                    r.animatingTo = Math.floor(r.currentSlide / r.move);
                    r.currentItem = r.currentSlide;
                    r.slides.removeClass(i + "active-slide").eq(r.currentItem).addClass(i + "active-slide");
                    r.slides.click(function(t) {
                        t.preventDefault();
                        var n = e(this),
                            s = n.index();
                        var o = n.offset().left - e(r).scrollLeft();
                        if (o <= 0 && n.hasClass(i + "active-slide")) {
                            r.flexAnimate(r.getTarget("prev"), true)
                        } else if (!e(r.vars.asNavFor).data("flexslider").animating && !n.hasClass(i + "active-slide")) {
                            r.direction = r.currentItem < s ? "next" : "prev";
                            r.flexAnimate(s, r.vars.pauseOnAction, false, true, true)
                        }
                    })
                }
            },
            controlNav: {
                setup: function() {
                    if (!r.manualControls) {
                        d.controlNav.setupPaging()
                    } else {
                        d.controlNav.setupManual()
                    }
                },
                setupPaging: function() {
                    var t = r.vars.controlNav === "thumbnails" ? "control-thumbs" : "control-paging",
                        n = 1,
                        s, a;
                    r.controlNavScaffold = e('<ol class="' + i + "control-nav " + i + t + '"></ol>');
                    if (r.pagingCount > 1) {
                        for (var f = 0; f < r.pagingCount; f++) {
                            a = r.slides.eq(f);
                            s = r.vars.controlNav === "thumbnails" ? '<img src="' + a.attr("data-thumb") + '"/>' : "<a>" + n + "</a>";
                            if ("thumbnails" === r.vars.controlNav && true === r.vars.thumbCaptions) {
                                var l = a.attr("data-thumbcaption");
                                if ("" != l && undefined != l) s += '<span class="' + i + 'caption">' + l + "</span>"
                            }
                            r.controlNavScaffold.append("<li>" + s + "</li>");
                            n++
                        }
                    }
                    r.controlsContainer ? e(r.controlsContainer).append(r.controlNavScaffold) : r.append(r.controlNavScaffold);
                    d.controlNav.set();
                    d.controlNav.active();
                    r.controlNavScaffold.delegate("a, img", o, function(t) {
                        t.preventDefault();
                        if (u === "" || u === t.type) {
                            var n = e(this),
                                s = r.controlNav.index(n);
                            if (!n.hasClass(i + "active")) {
                                r.direction = s > r.currentSlide ? "next" : "prev";
                                r.flexAnimate(s, r.vars.pauseOnAction)
                            }
                        }
                        if (u === "") {
                            u = t.type
                        }
                        d.setToClearWatchedEvent()
                    })
                },
                setupManual: function() {
                    r.controlNav = r.manualControls;
                    d.controlNav.active();
                    r.controlNav.bind(o, function(t) {
                        t.preventDefault();
                        if (u === "" || u === t.type) {
                            var n = e(this),
                                s = r.controlNav.index(n);
                            if (!n.hasClass(i + "active")) {
                                s > r.currentSlide ? r.direction = "next" : r.direction = "prev";
                                r.flexAnimate(s, r.vars.pauseOnAction)
                            }
                        }
                        if (u === "") {
                            u = t.type
                        }
                        d.setToClearWatchedEvent()
                    })
                },
                set: function() {
                    var t = r.vars.controlNav === "thumbnails" ? "img" : "a";
                    r.controlNav = e("." + i + "control-nav li " + t, r.controlsContainer ? r.controlsContainer : r)
                },
                active: function() {
                    r.controlNav.removeClass(i + "active").eq(r.animatingTo).addClass(i + "active")
                },
                update: function(t, n) {
                    if (r.pagingCount > 1 && t === "add") {
                        r.controlNavScaffold.append(e("<li><a>" + r.count + "</a></li>"))
                    } else if (r.pagingCount === 1) {
                        r.controlNavScaffold.find("li").remove()
                    } else {
                        r.controlNav.eq(n).closest("li").remove()
                    }
                    d.controlNav.set();
                    r.pagingCount > 1 && r.pagingCount !== r.controlNav.length ? r.update(n, t) : d.controlNav.active()
                }
            },
            directionNav: {
                setup: function() {
                    var t = e('<ul class="' + i + 'direction-nav"><li><a class="' + i + 'prev" href="#">' + r.vars.prevText + '</a></li><li><a class="' + i + 'next" href="#">' + r.vars.nextText + "</a></li></ul>");
                    if (r.controlsContainer) {
                        e(r.controlsContainer).append(t);
                        r.directionNav = e("." + i + "direction-nav li a", r.controlsContainer)
                    } else {
                        r.append(t);
                        r.directionNav = e("." + i + "direction-nav li a", r)
                    }
                    d.directionNav.update();
                    r.directionNav.bind(o, function(t) {
                        t.preventDefault();
                        var n;
                        if (u === "" || u === t.type) {
                            n = e(this).hasClass(i + "next") ? r.getTarget("next") : r.getTarget("prev");
                            r.flexAnimate(n, r.vars.pauseOnAction)
                        }
                        if (u === "") {
                            u = t.type
                        }
                        d.setToClearWatchedEvent()
                    })
                },
                update: function() {
                    var e = i + "disabled";
                    if (r.pagingCount === 1) {
                        r.directionNav.addClass(e).attr("tabindex", "-1")
                    } else if (!r.vars.animationLoop) {
                        if (r.animatingTo === 0) {
                            r.directionNav.removeClass(e).filter("." + i + "prev").addClass(e).attr("tabindex", "-1")
                        } else if (r.animatingTo === r.last) {
                            r.directionNav.removeClass(e).filter("." + i + "next").addClass(e).attr("tabindex", "-1")
                        } else {
                            r.directionNav.removeClass(e).removeAttr("tabindex")
                        }
                    } else {
                        r.directionNav.removeClass(e).removeAttr("tabindex")
                    }
                }
            },
            pausePlay: {
                setup: function() {
                    var t = e('<div class="' + i + 'pauseplay"><a></a></div>');
                    if (r.controlsContainer) {
                        r.controlsContainer.append(t);
                        r.pausePlay = e("." + i + "pauseplay a", r.controlsContainer)
                    } else {
                        r.append(t);
                        r.pausePlay = e("." + i + "pauseplay a", r)
                    }
                    d.pausePlay.update(r.vars.slideshow ? i + "pause" : i + "play");
                    r.pausePlay.bind(o, function(t) {
                        t.preventDefault();
                        if (u === "" || u === t.type) {
                            if (e(this).hasClass(i + "pause")) {
                                r.manualPause = true;
                                r.manualPlay = false;
                                r.pause()
                            } else {
                                r.manualPause = false;
                                r.manualPlay = true;
                                r.play()
                            }
                        }
                        if (u === "") {
                            u = t.type
                        }
                        d.setToClearWatchedEvent()
                    })
                },
                update: function(e) {
                    e === "play" ? r.pausePlay.removeClass(i + "pause").addClass(i + "play").text(r.vars.playText) : r.pausePlay.removeClass(i + "play").addClass(i + "pause").text(r.vars.pauseText)
                }
            },
            touch: function() {
                function v(o) {
                    if (r.animating) {
                        o.preventDefault()
                    } else if (window.navigator.msPointerEnabled || o.touches.length === 1) {
                        r.pause();
                        s = f ? r.h : r.w;
                        u = Number(new Date);
                        p = o.touches[0].pageX;
                        d = o.touches[0].pageY;
                        if (window.navigator.msPointerEnabled) {
                            p = o.pageX;
                            d = o.pageY
                        }
                        i = c && l && r.animatingTo === r.last ? 0 : c && l ? r.limit - (r.itemW + r.vars.itemMargin) * r.move * r.animatingTo : c && r.currentSlide === r.last ? r.limit : c ? (r.itemW + r.vars.itemMargin) * r.move * r.currentSlide : l ? (r.last - r.currentSlide + r.cloneOffset) * s : (r.currentSlide + r.cloneOffset) * s;
                        e = f ? d : p;
                        n = f ? p : d;
                        t.addEventListener("touchmove", m, false);
                        t.addEventListener("touchend", g, false);
                        if (window.navigator.msPointerEnabled) {
                            t.addEventListener("MSPointerMove", m, false);
                            t.addEventListener("MSPointerOut", g, false)
                        }
                    }
                }

                function m(t) {
                    if (window.navigator.msPointerEnabled) {
                        p = t.pageX;
                        d = t.pageY
                    } else {
                        p = t.touches[0].pageX;
                        d = t.touches[0].pageY
                    }
                    o = f ? e - d : e - p;
                    a = f ? Math.abs(o) < Math.abs(p - n) : Math.abs(o) < Math.abs(d - n);
                    if (window.navigator.msPointerEnabled) {
                        var l = 100
                    } else {
                        var l = 500
                    }
                    if (!a || Number(new Date) - u > l) {
                        t.preventDefault();
                        if (!h && r.transitions) {
                            if (!r.vars.animationLoop) {
                                o = o / (r.currentSlide === 0 && o < 0 || r.currentSlide === r.last && o > 0 ? Math.abs(o) / s + 2 : 1)
                            }
                            r.setProps(i + o, "setTouch")
                        }
                    }
                }

                function g(f) {
                    t.removeEventListener("touchmove", m, false);
                    if (window.navigator.msPointerEnabled) {
                        t.removeEventListener("MSPointerMove", m, false)
                    }
                    if (r.animatingTo === r.currentSlide && !a && !(o === null)) {
                        var c = l ? -o : o,
                            p = c > 0 ? r.getTarget("next") : r.getTarget("prev");
                        if (r.canAdvance(p) && (Number(new Date) - u < 550 && Math.abs(c) > 50 || Math.abs(c) > s / 2)) {
                            r.flexAnimate(p, r.vars.pauseOnAction)
                        } else {
                            if (!h) r.flexAnimate(r.currentSlide, r.vars.pauseOnAction, true)
                        }
                    }
                    t.removeEventListener("touchend", g, false);
                    if (window.navigator.msPointerEnabled) {
                        t.removeEventListener("MSPointerOut", g, false)
                    }
                    e = null;
                    n = null;
                    o = null;
                    i = null
                }
                var e, n, i, s, o, u, a = false;
                var p = 0;
                var d = 0;
                t.addEventListener("touchstart", v, false);
                if (window.navigator.msPointerEnabled) {
                    t.addEventListener("MSPointerDown", v, false)
                }
            },
            resize: function() {
                if (!r.animating && r.is(":visible")) {
                    if (!c) r.doMath();
                    if (h) {
                        d.smoothHeight()
                    } else if (c) {
                        r.slides.width(r.computedW);
                        r.update(r.pagingCount);
                        r.setProps()
                    } else if (f) {
                        r.viewport.height(r.h);
                        r.setProps(r.h, "setTotal")
                    } else {
                        if (r.vars.smoothHeight) d.smoothHeight();
                        r.newSlides.width(r.computedW);
                        r.setProps(r.computedW, "setTotal")
                    }
                }
            },
            smoothHeight: function(e) {
                if (!f || h) {
                    var t = h ? r : r.viewport;
                    e ? t.animate({
                        height: r.slides.eq(r.animatingTo).height()
                    }, e) : t.height(r.slides.eq(r.animatingTo).height())
                }
            },
            sync: function(t) {
                var n = e(r.vars.sync).data("flexslider"),
                    i = r.animatingTo;
                switch (t) {
                    case "animate":
                        n.flexAnimate(i, r.vars.pauseOnAction, false, true);
                        break;
                    case "play":
                        if (!n.playing && !n.asNav) {
                            n.play()
                        }
                        break;
                    case "pause":
                        n.pause();
                        break
                }
            },
            setToClearWatchedEvent: function() {
                clearTimeout(a);
                a = setTimeout(function() {
                    u = ""
                }, 3e3)
            }
        };
        r.flexAnimate = function(t, n, o, u, a) {
            if (t !== r.currentSlide) {
                r.direction = t > r.currentSlide ? "next" : "prev"
            }
            if (p && r.pagingCount === 1) r.direction = r.currentItem < t ? "next" : "prev";
            if (!r.animating && (r.canAdvance(t, a) || o) && r.is(":visible")) {
                if (p && u) {
                    var v = e(r.vars.asNavFor).data("flexslider");
                    r.atEnd = t === 0 || t === r.count - 1;
                    v.flexAnimate(t, true, false, true, a);
                    r.direction = r.currentItem < t ? "next" : "prev";
                    v.direction = r.direction;
                    if (Math.ceil((t + 1) / r.visible) - 1 !== r.currentSlide && t !== 0) {
                        r.currentItem = t;
                        r.slides.removeClass(i + "active-slide").eq(t).addClass(i + "active-slide");
                        t = Math.floor(t / r.visible)
                    } else {
                        r.currentItem = t;
                        r.slides.removeClass(i + "active-slide").eq(t).addClass(i + "active-slide");
                        return false
                    }
                }
                r.animating = true;
                r.animatingTo = t;
                r.vars.before(r);
                if (n) r.pause();
                if (r.syncExists && !a) d.sync("animate");
                if (r.vars.controlNav) d.controlNav.active();
                if (!c) r.slides.removeClass(i + "active-slide").eq(t).addClass(i + "active-slide");
                r.atEnd = t === 0 || t === r.last;
                if (r.vars.directionNav) d.directionNav.update();
                if (t === r.last) {
                    r.vars.end(r);
                    if (!r.vars.animationLoop) r.pause()
                }
                if (!h) {
                    var m = f ? r.slides.filter(":first").height() : r.computedW,
                        g, y, b;
                    if (c) {
                        g = r.vars.itemMargin;
                        b = (r.itemW + g) * r.move * r.animatingTo;
                        y = b > r.limit && r.visible !== 1 ? r.limit : b
                    } else if (r.currentSlide === 0 && t === r.count - 1 && r.vars.animationLoop && r.direction !== "next") {
                        y = l ? (r.count + r.cloneOffset) * m : 0
                    } else if (r.currentSlide === r.last && t === 0 && r.vars.animationLoop && r.direction !== "prev") {
                        y = l ? 0 : (r.count + 1) * m
                    } else {
                        y = l ? (r.count - 1 - t + r.cloneOffset) * m : (t + r.cloneOffset) * m
                    }
                    r.setProps(y, "", r.vars.animationSpeed);
                    if (r.transitions) {
                        if (!r.vars.animationLoop || !r.atEnd) {
                            r.animating = false;
                            r.currentSlide = r.animatingTo
                        }
                        r.container.unbind("webkitTransitionEnd transitionend");
                        r.container.bind("webkitTransitionEnd transitionend", function() {
                            r.wrapup(m)
                        })
                    } else {
                        r.container.animate(r.args, r.vars.animationSpeed, r.vars.easing, function() {
                            r.wrapup(m)
                        })
                    }
                } else {
                    if (!s) {
                        r.slides.eq(r.currentSlide).css({
                            zIndex: 1
                        }).animate({
                            opacity: 0
                        }, r.vars.animationSpeed, r.vars.easing);
                        r.slides.eq(t).css({
                            zIndex: 2
                        }).animate({
                            opacity: 1
                        }, r.vars.animationSpeed, r.vars.easing, r.wrapup)
                    } else {
                        r.slides.eq(r.currentSlide).css({
                            opacity: 0,
                            zIndex: 1
                        });
                        r.slides.eq(t).css({
                            opacity: 1,
                            zIndex: 2
                        });
                        r.wrapup(m)
                    }
                }
                if (r.vars.smoothHeight) d.smoothHeight(r.vars.animationSpeed)
            }
        };
        r.wrapup = function(e) {
            if (!h && !c) {
                if (r.currentSlide === 0 && r.animatingTo === r.last && r.vars.animationLoop) {
                    r.setProps(e, "jumpEnd")
                } else if (r.currentSlide === r.last && r.animatingTo === 0 && r.vars.animationLoop) {
                    r.setProps(e, "jumpStart")
                }
            }
            r.animating = false;
            r.currentSlide = r.animatingTo;
            r.vars.after(r)
        };
        r.animateSlides = function() {
            if (!r.animating && focused) r.flexAnimate(r.getTarget("next"))
        };
        r.pause = function() {
            clearInterval(r.animatedSlides);
            r.animatedSlides = null;
            r.playing = false;
            if (r.vars.pausePlay) d.pausePlay.update("play");
            if (r.syncExists) d.sync("pause")
        };
        r.play = function() {
            r.animatedSlides = r.animatedSlides || setInterval(r.animateSlides, r.vars.slideshowSpeed);
            r.playing = true;
            if (r.vars.pausePlay) d.pausePlay.update("pause");
            if (r.syncExists) d.sync("play")
        };
        r.stop = function() {
            r.pause();
            r.stopped = true
        };
        r.canAdvance = function(e, t) {
            var n = p ? r.pagingCount - 1 : r.last;
            return t ? true : p && r.currentItem === r.count - 1 && e === 0 && r.direction === "prev" ? true : p && r.currentItem === 0 && e === r.pagingCount - 1 && r.direction !== "next" ? false : e === r.currentSlide && !p ? false : r.vars.animationLoop ? true : r.atEnd && r.currentSlide === 0 && e === n && r.direction !== "next" ? false : r.atEnd && r.currentSlide === n && e === 0 && r.direction === "next" ? false : true
        };
        r.getTarget = function(e) {
            r.direction = e;
            if (e === "next") {
                return r.currentSlide === r.last ? 0 : r.currentSlide + 1
            } else {
                return r.currentSlide === 0 ? r.last : r.currentSlide - 1
            }
        };
        r.setProps = function(e, t, n) {
            var i = function() {
                var n = e ? e : (r.itemW + r.vars.itemMargin) * r.move * r.animatingTo,
                    i = function() {
                        if (c) {
                            return t === "setTouch" ? e : l && r.animatingTo === r.last ? 0 : l ? r.limit - (r.itemW + r.vars.itemMargin) * r.move * r.animatingTo : r.animatingTo === r.last ? r.limit : n
                        } else {
                            switch (t) {
                                case "setTotal":
                                    return l ? (r.count - 1 - r.currentSlide + r.cloneOffset) * e : (r.currentSlide + r.cloneOffset) * e;
                                case "setTouch":
                                    return l ? e : e;
                                case "jumpEnd":
                                    return l ? e : r.count * e;
                                case "jumpStart":
                                    return l ? r.count * e : e;
                                default:
                                    return e
                            }
                        }
                    }();
                return i * -1 + "px"
            }();
            if (r.transitions) {
                i = f ? "translate3d(0," + i + ",0)" : "translate3d(" + i + ",0,0)";
                n = n !== undefined ? n / 1e3 + "s" : "0s";
                r.container.css("-" + r.pfx + "-transition-duration", n)
            }
            r.args[r.prop] = i;
            if (r.transitions || n === undefined) r.container.css(r.args)
        };
        r.setup = function(t) {
            if (!h) {
                var n, o;
                if (t === "init") {
                    r.viewport = e('<div class="' + i + 'viewport"></div>').css({
                        overflow: "hidden",
                        position: "relative"
                    }).appendTo(r).append(r.container);
                    r.cloneCount = 0;
                    r.cloneOffset = 0;
                    if (l) {
                        o = e.makeArray(r.slides).reverse();
                        r.slides = e(o);
                        r.container.empty().append(r.slides)
                    }
                }
                if (r.vars.animationLoop && !c) {
                    r.cloneCount = 2;
                    r.cloneOffset = 1;
                    if (t !== "init") r.container.find(".clone").remove();
                    r.container.append(r.slides.first().clone().addClass("clone").attr("aria-hidden", "true")).prepend(r.slides.last().clone().addClass("clone").attr("aria-hidden", "true"))
                }
                r.newSlides = e(r.vars.selector, r);
                n = l ? r.count - 1 - r.currentSlide + r.cloneOffset : r.currentSlide + r.cloneOffset;
                if (f && !c) {
                    r.container.height((r.count + r.cloneCount) * 200 + "%").css("position", "absolute").width("100%");
                    setTimeout(function() {
                        r.newSlides.css({
                            display: "block"
                        });
                        r.doMath();
                        r.viewport.height(r.h);
                        r.setProps(n * r.h, "init")
                    }, t === "init" ? 100 : 0)
                } else {
                    r.container.width((r.count + r.cloneCount) * 200 + "%");
                    r.setProps(n * r.computedW, "init");
                    setTimeout(function() {
                        r.doMath();
                        r.newSlides.css({
                            width: r.computedW,
                            "float": "left",
                            display: "block"
                        });
                        if (r.vars.smoothHeight) d.smoothHeight()
                    }, t === "init" ? 100 : 0)
                }
            } else {
                r.slides.css({
                    width: "100%",
                    "float": "left",
                    marginRight: "-100%",
                    position: "relative"
                });
                if (t === "init") {
                    if (!s) {
                        r.slides.css({
                            opacity: 0,
                            display: "block",
                            zIndex: 1
                        }).eq(r.currentSlide).css({
                            zIndex: 2
                        }).animate({
                            opacity: 1
                        }, r.vars.animationSpeed, r.vars.easing)
                    } else {
                        r.slides.css({
                            opacity: 0,
                            display: "block",
                            webkitTransition: "opacity " + r.vars.animationSpeed / 1e3 + "s ease",
                            zIndex: 1
                        }).eq(r.currentSlide).css({
                            opacity: 1,
                            zIndex: 2
                        })
                    }
                }
                if (r.vars.smoothHeight) d.smoothHeight()
            }
            if (!c) r.slides.removeClass(i + "active-slide").eq(r.currentSlide).addClass(i + "active-slide")
        };
        r.doMath = function() {
            var e = r.slides.first(),
                t = r.vars.itemMargin,
                n = r.vars.minItems,
                i = r.vars.maxItems;
            r.w = r.width();
            r.h = e.height();
            r.boxPadding = e.outerWidth() - e.width();
            if (c) {
                r.itemT = r.vars.itemWidth + t;
                r.minW = n ? n * r.itemT : r.w;
                r.maxW = i ? i * r.itemT - t : r.w;
                r.itemW = r.minW > r.w ? (r.w - t * (n - 1)) / n : r.maxW < r.w ? (r.w - t * (i - 1)) / i : r.vars.itemWidth > r.w ? r.w : r.vars.itemWidth;
                r.visible = Math.floor(r.w / r.itemW);
                r.move = r.vars.move > 0 && r.vars.move < r.visible ? r.vars.move : r.visible;
                r.pagingCount = Math.ceil((r.count - r.visible) / r.move + 1);
                r.last = r.pagingCount - 1;
                r.limit = r.pagingCount === 1 ? 0 : r.vars.itemWidth > r.w ? r.itemW * (r.count - 1) + t * (r.count - 1) : (r.itemW + t) * r.count - r.w - t
            } else {
                r.itemW = r.w;
                r.pagingCount = r.count;
                r.last = r.count - 1
            }
            r.computedW = r.itemW - r.boxPadding
        };
        r.update = function(e, t) {
            r.doMath();
            if (!c) {
                if (e < r.currentSlide) {
                    r.currentSlide += 1
                } else if (e <= r.currentSlide && e !== 0) {
                    r.currentSlide -= 1
                }
                r.animatingTo = r.currentSlide
            }
            if (r.vars.controlNav && !r.manualControls) {
                if (t === "add" && !c || r.pagingCount > r.controlNav.length) {
                    d.controlNav.update("add")
                } else if (t === "remove" && !c || r.pagingCount < r.controlNav.length) {
                    if (c && r.currentSlide > r.last) {
                        r.currentSlide -= 1;
                        r.animatingTo -= 1
                    }
                    d.controlNav.update("remove", r.last)
                }
            }
            if (r.vars.directionNav) d.directionNav.update()
        };
        r.addSlide = function(t, n) {
            var i = e(t);
            r.count += 1;
            r.last = r.count - 1;
            if (f && l) {
                n !== undefined ? r.slides.eq(r.count - n).after(i) : r.container.prepend(i)
            } else {
                n !== undefined ? r.slides.eq(n).before(i) : r.container.append(i)
            }
            r.update(n, "add");
            r.slides = e(r.vars.selector + ":not(.clone)", r);
            r.setup();
            r.vars.added(r)
        };
        r.removeSlide = function(t) {
            var n = isNaN(t) ? r.slides.index(e(t)) : t;
            r.count -= 1;
            r.last = r.count - 1;
            if (isNaN(t)) {
                e(t, r.slides).remove()
            } else {
                f && l ? r.slides.eq(r.last).remove() : r.slides.eq(t).remove()
            }
            r.doMath();
            r.update(n, "remove");
            r.slides = e(r.vars.selector + ":not(.clone)", r);
            r.setup();
            r.vars.removed(r)
        };
        d.init()
    };
    e(window).blur(function(e) {
        focused = false
    }).focus(function(e) {
        focused = true
    });
    e.flexslider.defaults = {
        namespace: "flex-",
        selector: ".slides > li",
        animation: "fade",
        easing: "swing",
        direction: "horizontal",
        reverse: false,
        animationLoop: true,
        smoothHeight: false,
        startAt: 0,
        slideshow: true,
        slideshowSpeed: 7e3,
        animationSpeed: 600,
        initDelay: 0,
        randomize: false,
        thumbCaptions: false,
        pauseOnAction: true,
        pauseOnHover: false,
        useCSS: true,
        touch: true,
        video: false,
        controlNav: true,
        directionNav: true,
        prevText: "Previous",
        nextText: "Next",
        keyboard: true,
        multipleKeyboard: false,
        mousewheel: false,
        pausePlay: false,
        pauseText: "Pause",
        playText: "Play",
        controlsContainer: "",
        manualControls: "",
        sync: "",
        asNavFor: "",
        itemWidth: 0,
        itemMargin: 0,
        minItems: 0,
        maxItems: 0,
        move: 0,
        start: function() {},
        before: function() {},
        after: function() {},
        end: function() {},
        added: function() {},
        removed: function() {}
    };
    e.fn.flexslider = function(t) {
        if (t === undefined) t = {};
        if (typeof t === "object") {
            return this.each(function() {
                var n = e(this),
                    r = t.selector ? t.selector : ".slides > li",
                    i = n.find(r);
                if (i.length === 1) {
                    i.fadeIn(400);
                    if (t.start) t.start(n)
                } else if (n.data("flexslider") === undefined) {
                    new e.flexslider(this, t)
                }
            })
        } else {
            var n = e(this).data("flexslider");
            switch (t) {
                case "play":
                    n.play();
                    break;
                case "pause":
                    n.pause();
                    break;
                case "stop":
                    n.stop();
                    break;
                case "next":
                    n.flexAnimate(n.getTarget("next"), true);
                    break;
                case "prev":
                case "previous":
                    n.flexAnimate(n.getTarget("prev"), true);
                    break;
                default:
                    if (typeof t === "number") n.flexAnimate(t, true)
            }
        }
    }
})(jQuery);
(function(e) {
    e.fn.reverse = [].reverse;
    e.fn.shift = [].shift;
    e.fn.simpletreeview = function(t) {
        function i(t) {
            if (t.size() == 0) return;
            var n = e(t.get(0));
            t.shift();
            o(n, "open", function() {
                i(t)
            })
        }

        function s(e) {
            if (e.parent("li").size() == 0) return;
            o(e, "close", function() {
                s(e.parent("li").parent("ul"))
            })
        }

        function o(e, t, r) {
            if (r === undefined) r = function() {};
            var i = e.parent("li").children("span.handle");
            if (t == "open") {
                i.html(n.open);
                if (n.slide) {
                    e.slideDown(n.speed, r)
                } else {
                    e.show();
                    r()
                }
            } else if (t == "close") {
                i.html(n.close);
                if (n.slide) {
                    e.slideUp(n.speed, r)
                } else {
                    e.hide();
                    r()
                }
            } else {
                i.html(e.is(":hidden") ? n.open : n.close);
                if (n.slide) {
                    e.slideToggle(n.speed, r)
                } else {
                    e.toggle();
                    r()
                }
            }
        }

        function u(t) {
            t.each(function() {
                var t = e(this);
                var r = t.children("ul");
                var i = r.children("li");
                if (i.size() > 0) {
                    t.prepend('<span class="handle">' + (n.collapsed || r.is(":hidden") ? n.close : n.open) + "</span>");
                    if (n.collapsed) {
                        r.hide()
                    }
                    t.children("span.handle").click(function() {
                        o(r)
                    });
                    u(i)
                }
            })
        }
        var n = e.extend({}, {
            open: "&#9660;",
            close: "&#9658;",
            slide: false,
            speed: "normal",
            collapsed: false,
            collapse: null,
            expand: null
        }, t);
        var r = e(this);
        this.expand = function(e) {
            var t = this.getNode(e).parents("ul").reverse().andSelf();
            t.shift();
            i(t)
        };
        this.collapse = function(e) {
            s(this.getNode(e))
        };
        this.getNode = function(t) {
            if (typeof t != "object") {
                selector = e.map(t.toString().split("."), function(e) {
                    return "li:eq(" + e + ") > ul"
                }).join(" > ");
                t = r.find(">" + selector)
            }
            return t
        };
        u(r.children("li"));
        if (n.expand) {
            this.expand(n.expand)
        }
        if (n.collapse) {
            this.collapse(n.collapse)
        }
        return this
    }
})(jQuery);
(function(e) {
    function t(t, n) {
        return parseInt(e.css(t[0], n)) || 0
    }

    function n(e) {
        return e[0].offsetWidth + t(e, "marginLeft") + t(e, "marginRight")
    }

    function r(e) {
        return e[0].offsetHeight + t(e, "marginTop") + t(e, "marginBottom")
    }
    e.fn.jCarouselLite = function(t) {
        t = e.extend({
            btnPrev: null,
            btnNext: null,
            btnGo: null,
            mouseWheel: false,
            auto: null,
            hoverPause: false,
            speed: 200,
            easing: null,
            vertical: false,
            circular: true,
            visible: 3,
            start: 0,
            scroll: 1,
            beforeStart: null,
            afterEnd: null
        }, t || {});
        return this.each(function() {
            function w() {
                E();
                b = setInterval(function() {
                    x(v + t.scroll)
                }, t.auto + t.speed)
            }

            function E() {
                clearInterval(b)
            }

            function S() {
                return p.slice(v).slice(0, h)
            }

            function x(n) {
                if (!i) {
                    if (t.beforeStart) t.beforeStart.call(this, S());
                    if (t.circular) {
                        if (n < 0) {
                            f.css(s, -((v + c) * m) + "px");
                            v = n + c
                        } else if (n > d - h) {
                            f.css(s, -((v - c) * m) + "px");
                            v = n - c
                        } else v = n
                    } else {
                        if (n < 0 || n > d - h) return;
                        else v = n
                    }
                    i = true;
                    f.animate(s == "left" ? {
                        left: -(v * m)
                    } : {
                        top: -(v * m)
                    }, t.speed, t.easing, function() {
                        if (t.afterEnd) t.afterEnd.call(this, S());
                        i = false
                    });
                    if (!t.circular) {
                        e(t.btnPrev + "," + t.btnNext).removeClass("disabled");
                        e(v - t.scroll < 0 && t.btnPrev || v + t.scroll > d - h && t.btnNext || []).addClass("disabled")
                    }
                }
                return false
            }
            var i = false,
                s = t.vertical ? "top" : "left",
                u = t.vertical ? "height" : "width";
            var a = e(this),
                f = e("ul", a),
                l = e("li", f),
                c = l.size(),
                h = t.visible;
            if (t.circular) {
                f.prepend(l.slice(c - h + 1).clone()).append(l.slice(0, t.scroll).clone());
                t.start += h - 1
            }
            var p = e("li", f),
                d = p.size(),
                v = t.start;
            a.css("visibility", "visible");
            p.css({
                overflow: "hidden",
                "float": t.vertical ? "none" : "left"
            });
            f.css({
                margin: "0",
                padding: "0",
                position: "relative",
                "list-style-type": "none",
                "z-index": "1"
            });
            a.css({
                overflow: "hidden",
                position: "relative",
                "z-index": "2",
                left: "0px"
            });
            var m = t.vertical ? r(p) : n(p);
            var g = m * d;
            var y = m * h;
            p.css({
                width: p.width(),
                height: p.height()
            });
            f.css(u, g + "px").css(s, -(v * m));
            a.css(u, y + "px");
            if (t.btnPrev) {
                e(t.btnPrev).click(function() {
                    return x(v - t.scroll)
                });
                if (t.hoverPause) {
                    e(t.btnPrev).hover(function() {
                        E()
                    }, function() {
                        w()
                    })
                }
            }
            if (t.btnNext) {
                e(t.btnNext).click(function() {
                    return x(v + t.scroll)
                });
                if (t.hoverPause) {
                    e(t.btnNext).hover(function() {
                        E()
                    }, function() {
                        w()
                    })
                }
            }
            if (t.btnGo) e.each(t.btnGo, function(n, r) {
                e(r).click(function() {
                    return x(t.circular ? t.visible + n : n)
                })
            });
            if (t.mouseWheel && a.mousewheel) a.mousewheel(function(e, n) {
                return n > 0 ? x(v - t.scroll) : x(v + t.scroll)
            });
            var b;
            if (t.auto) {
                if (t.hoverPause) {
                    a.hover(function() {
                        E()
                    }, function() {
                        w()
                    })
                }
                w()
            }
        })
    };
})(jQuery);
var ss = {
    fixAllLinks: function() {
        var e = document.getElementsByTagName("a");
        for (var t = 0; t < e.length; t++) {
            var n = e[t];
            if (n.href && n.href.indexOf("#") != -1 && (n.pathname == location.pathname || "/" + n.pathname == location.pathname) && n.search == location.search) {
                ss.addEvent(n, "click", ss.smoothScroll)
            }
        }
    },
    smoothScroll: function(e) {
        if (window.event) {
            target = window.event.srcElement
        } else if (e) {
            target = e.target
        } else return;
        if (target.nodeName.toLowerCase() != "a") {
            target = target.parentNode
        }
        if (target.nodeName.toLowerCase() != "a") return;
        anchor = target.hash.substr(1);
        var t = document.getElementsByTagName("a");
        var n = null;
        for (var r = 0; r < t.length; r++) {
            var i = t[r];
            if (i.name && i.name == anchor) {
                n = i;
                break
            }
        }
        if (!n) n = document.getElementById(anchor);
        if (!n) return true;
        var s = n.offsetLeft;
        var o = n.offsetTop;
        var u = n;
        while (u.offsetParent && u.offsetParent != document.body) {
            u = u.offsetParent;
            s += u.offsetLeft;
            o += u.offsetTop
        }
        clearInterval(ss.INTERVAL);
        cypos = ss.getCurrentYPos();
        ss_stepsize = parseInt((o - cypos) / ss.STEPS);
        var a = document.getElementById(anchor);
        if (a.style.position != "fixed") {
            ss.INTERVAL = setInterval("ss.scrollWindow(" + ss_stepsize + "," + o + ',"' + anchor + '")', 10)
        }
        if (window.event) {
            window.event.cancelBubble = true;
            window.event.returnValue = false
        }
        if (e && e.preventDefault && e.stopPropagation) {
            e.preventDefault();
            e.stopPropagation()
        }
    },
    scrollWindow: function(e, t, n) {
        wascypos = ss.getCurrentYPos();
        isAbove = wascypos < t;
        window.scrollTo(0, wascypos + e);
        iscypos = ss.getCurrentYPos();
        isAboveNow = iscypos < t;
        if (isAbove != isAboveNow || wascypos == iscypos) {
            window.scrollTo(0, t);
            clearInterval(ss.INTERVAL);
            location.hash = n
        }
    },
    getCurrentYPos: function() {
        if (document.body && document.body.scrollTop) return document.body.scrollTop;
        if (document.documentElement && document.documentElement.scrollTop) return document.documentElement.scrollTop;
        if (window.pageYOffset) return window.pageYOffset;
        return 0
    },
    addEvent: function(e, t, n, r) {
        if (e.addEventListener) {
            e.addEventListener(t, n, r);
            return true
        } else if (e.attachEvent) {
            var i = e.attachEvent("on" + t, n);
            return i
        } else {
            alert("Handler could not be removed")
        }
    }
};
ss.STEPS = 25;
ss.addEvent(window, "load", ss.fixAllLinks);