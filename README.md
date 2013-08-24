KudzuPHP
========

An event driven template engine for PHP.

KudzuPHP is yet another template engine written in PHP.  It is the result of converting the KudzuASP template engine to PHP.  The orignal engine code was written in Visual Basic 5 and leveraged COM for objects.  Just after the advent of ASP 3 the engine was recoded in VBScript because at that time there was no template engine for that platform.  A conversion to .NET in C# also exists.

KudzuPHP and it's predecessors differed from template engines of their time by being event driven.  Once the template engine was invoked it made callbacks into the code that hosted it based on a hierarchical tag structure.  This allowed the templates to drive the output rather than the hosting code.  In KudzuPHP the template can very often be radically changed without recoding the supporting code.

The engine supports a modest set of useful tags for repetition of content, conditional content, content substitution, macros, include directives. Custom tag handlers are also supported.

The engine was designed to be easy to use and easy to extend.  The engine implements a library management system that allows you to author your custom template tags and put them into standalone libraries that can be shared.

more to follow...


