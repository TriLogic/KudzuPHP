KudzuPHP
========

An event driven template engine for PHP.

KudzuPHP is yet another template engine written in PHP.  It is different from most template engines due to the fact it is event driven.  Once the template engine is invoked it makes callbacks into the hosting code based on the structure within the template.  This allows the template rather than the hosting code page to drive output.  In KudzuPHP the template can radically changed without needing to recode the supporting code.

The engine supports a modest set of useful tags for repetition of content, conditional content, content substitution, macros, include directives. Custom tag handlers are also supported and very easily implemented either within the hosting code page or in libraries.

The engine implements a library management system that makes it easy to author your own custom template tags and put them into standalone libraries that can be shared.

KudzuPHP is used in the Wordpress plugin named "Kazoo" where additional tag libraries provide hooks into Wordpress itself.
