/* Modernizr 2.5.3 (Custom Build) | MIT & BSD
 * Build: http://modernizr.com/download/#-video
 */
;
window.Modernizr = function(a, b, c) {
    function t(a) {
        i.cssText = a
    }

    function u(a, b) {
        return t(prefixes.join(a + ";") + (b || ""))
    }

    function v(a, b) {
        return typeof a === b
    }

    function w(a, b) {
        return!!~("" + a).indexOf(b)
    }

    function x(a, b, d) {
        for (var e in a) {
            var f = b[a[e]];
            if (f !== c)return d === !1 ? a[e] : v(f, "function") ? f.bind(d || b) : f
        }
        return!1
    }

    var d = "2.5.3", e = {}, f = b.documentElement, g = "modernizr", h = b.createElement(g), i = h.style, j, k = {}.toString, l = {}, m = {}, n = {}, o = [], p = o.slice, q, r = {}.hasOwnProperty, s;
    !v(r, "undefined") && !v(r.call, "undefined") ? s = function(a, b) {
        return r.call(a, b)
    } : s = function(a, b) {
        return b in a && v(a.constructor.prototype[b], "undefined")
    }, Function.prototype.bind || (Function.prototype.bind = function(b) {
        var c = this;
        if (typeof c != "function")throw new TypeError;
        var d = p.call(arguments, 1), e = function() {
            if (this instanceof e) {
                var a = function() {
                };
                a.prototype = c.prototype;
                var f = new a, g = c.apply(f, d.concat(p.call(arguments)));
                return Object(g) === g ? g : f
            }
            return c.apply(b, d.concat(p.call(arguments)))
        };
        return e
    }), l.video = function() {
        var a = b.createElement("video"), c = !1;
        try {
            if (c = !!a.canPlayType)c = new Boolean(c), c.ogg = a.canPlayType('video/ogg; codecs="theora"').replace(/^no$/, ""), c.h264 = a.canPlayType('video/mp4; codecs="avc1.42E01E"').replace(/^no$/, ""), c.webm = a.canPlayType('video/webm; codecs="vp8, vorbis"').replace(/^no$/, "")
        } catch(d) {
        }
        return c
    };
    for (var y in l)s(l, y) && (q = y.toLowerCase(), e[q] = l[y](), o.push((e[q] ? "" : "no-") + q));
    return t(""), h = j = null, e._version = d, e
}(this, this.document);