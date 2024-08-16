<?php
// DDEV's built in xhprof handler breaks our own.
// We'll temporarily override it here, but return control back later.
// If you don't want this behavior, comment out the hooks in ".ddev/config.xhgui.yaml".
return;
