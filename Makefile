DESTDIR =
PREFIX  = /opt/openpom
TOPDIR := $(dir $(lastword $(MAKEFILE_LIST)))

EXCLUDE = .git \
          Makefile \
          "*.swp" \
          "*~" \
          .dummy

ifeq ($(V),1)
    INSTALL_Q = -v
else
    INSTALL_Q =
endif

findopts = $(foreach e,$(EXCLUDE),-name $(e) -prune -o) -true

.PHONY: install
install:
	for f in $$(find $(TOPDIR) $(findopts) -print); do \
	    if [ -d "$$f" ]; then \
	        install $(INSTALL_Q) -d -m 0755 $(DESTDIR)$(PREFIX)/$$f; \
	    else \
	        install $(INSTALL_Q) -m 0644 $$f $(DESTDIR)$(PREFIX)/$${f%/*}; \
	    fi; \
	done
