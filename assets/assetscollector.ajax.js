$.nette.ext('assetscollector', {
	success: function () {
		var re = /<!-- assets: (.*) -->/gmi;
		var str = document.documentElement.outerHTML;
		var m;
		var t;
		var l = [];

		while ((m = re.exec(str)) !== null) {
			if (m.index === re.lastIndex) {
				re.lastIndex++;
			}
			try {
				t = JSON.parse(m[1]);
			} catch (e) {
			}
			if (Array.isArray(t)) {
				l = l.concat(t);
			}
		}

		l = l.filter(function(value, index, self) {
			return self.indexOf(value) === index;
		});

		$.each(l, function(i, e) {
			if ($('script[src="' + e + '"]').length === 0 && $('link[href="' + e + '"]').length === 0) {
				ext = e.split('.');
				ext = ext[ext.length - 1];
				if (ext === 'js') {
					$("html").append('<script src="' + e + '"></script>');
					delete l[i];
				} else if (ext === 'css') {
					$("html").append('<link rel="stylesheet" type="text/css" href="' + e + '">');
					delete l[i];
				}
			}
		});
	}
});
