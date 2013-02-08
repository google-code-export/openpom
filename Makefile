DESTDIR =
PREFIX  = /usr/local
WWWDIR  = $(PREFIX)/www/openpom

TOPDIR := $(dir $(lastword $(MAKEFILE_LIST)))

F_BASE  = $(wildcard $(TOPDIR)/*.php) \
          $(wildcard $(TOPDIR)/*.css) \
          $(wildcard $(TOPDIR)/*.txt) \
          $(TOPDIR)/send-order \
          $(TOPDIR)/LICENSE \
          $(TOPDIR)/README

F_JS    = $(wildcard $(TOPDIR)/js/*) 
F_IMG   = $(wildcard $(TOPDIR)/img/*) 

ifeq ($(V),1)
    INSTALL_Q = -v
else
    INSTALL_Q =
endif

install:
	install $(INSTALL_Q) -d -m 0755 $(DESTDIR)$(WWWDIR)
	for f in $(F_BASE); do \
	    install $(INSTALL_Q) -m 0644 $$f $(DESTDIR)$(WWWDIR); \
	done
	
	install $(INSTALL_Q) -d -m 0755 $(DESTDIR)$(WWWDIR)/img
	for f in $(F_IMG); do \
	    install $(INSTALL_Q) -m 0644 $$f $(DESTDIR)$(WWWDIR)/img; \
	done
	
	install $(INSTALL_Q) -d -m 0755 $(DESTDIR)$(WWWDIR)/js
	for f in $(F_JS); do \
	    install $(INSTALL_Q) -m 0644 $$f $(DESTDIR)$(WWWDIR)/js; \
	done
