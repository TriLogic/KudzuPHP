KudzuPHP
========

An event driven template engine for PHP.

KudzuPHP is yet another template engine written in PHP to templating content.  It is the result of converting the KudzuASP template engine to PHP.  The orignal engine code was written in Visual Basic 5 and leveraged COM for objects.  Just after the advent of ASP 3 the engine was recoded in VBScript because at that time there was no template engine for that platform.  A conversion to .NET in C# also exists.

KudzuPHP and it's predecessors differed from template engines of their time by being event driven.  Once the template engine was invoked it made callbacks into the code that hosted it based on a hierarchical tag structure.  This allowed the temaplte to drive the output rather than the hosting code.

The engine supports a modest set of useful tags for reptition of content, conditional content, and content substitution.   It supports If..Then..Else, ItTrue, IfFalse, and even Switch...Case.  Custom tag handlers are also supported.

The engine was designed to be extremely easy to use and easy to extend.  Writing a custom tag handler and implementing them is often easier than combining generic tag constructs to achieve the same goal.

KudzuPHP sports a web-centric tagging scheme.... more to follow...


