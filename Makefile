DESTDIR =
PREFIX = /usr/local
WWWDIR = $(PREFIX)/www

TAG = $(shell git describe --tags --candidates=1)

install:
	cd src && for dirs in $$(find . -type d); do install -v -d -m 0755 $(DESTDIR)$(WWWDIR)/$$dirs; done
	cd src && for file in $$(find . -type f); do install -v -o 0 -g 0 -m 0644 $$file $(DESTDIR)$(WWWDIR)/$$file; done

pack:
	git archive --prefix='openpom-$(TAG)/' --format=tar $(TAG) | gzip > openpom-$(TAG).tar.gz

