DESTDIR =
PREFIX = /usr/local
WWWDIR = $(PREFIX)/www

ifeq ($(V),1)
    INSTALL_Q = -v
else
    INSTALL_Q =
endif

install:
	cd src && for dirs in $$(find . -type d); do install $(INSTALL_Q) -d -m 0755 $(DESTDIR)$(WWWDIR)/$$dirs; done
	cd src && for file in $$(find . -type f); do install $(INSTALL_Q) -m 0644 $$file $(DESTDIR)$(WWWDIR)/$$file; done
