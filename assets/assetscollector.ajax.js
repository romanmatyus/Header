$.nette.ext('assetscollector', {
	success: function () {
		var re = /<!-- assets: (.*) -->/gmi;
		var str = document.documentElement.outerHTML;
		var m;
		var t;
		var css = [];
		var js = [];

		while ((m = re.exec(str)) !== null) {
			if (m.index === re.lastIndex) {
				re.lastIndex++;
			}
			try {
				t = JSON.parse(m[1]);
			} catch (e) {
			}
			if (typeof(t) === 'object') {
				if (t.hasOwnProperty("css")) {
					css = css.concat(t["css"]);
				}
				if (t.hasOwnProperty("js")) {
					js = js.concat(t["js"]);
				}
			}
		}

		$.each(css, function(i, e) {
			if ($('link[href="' + e + '"]').length === 0) {
				$("html").append('<link rel="stylesheet" type="text/css" href="' + e + '">');
			}
		});

		$.each(js, function(i, e) {
			if ($('script[src="' + e + '"]').length === 0) {
				$("html").append('<script src="' + e + '"></script>');
			}
		});
	}
});
