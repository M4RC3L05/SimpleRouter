#2.6.1

-   code refactors

#2.6.0

-   Lazy loading the next request, (only when called \$next, a new request will be looked)
-   bug fixes

#2.5.1

-   Add method to so that handlers can be register to specifique
    group of methods
-   Making set custom data to the request object possible by using
    the magic methods **set and **get
-   updating dependencies versions

#2.4.1

-   Makes the error string passes on the next callback creates an expeption
-   bug fixes

#2.4.0

-   Now, call withViewData will appends the provided data insted of replace

#2.3.2

-   Fix bug when no handlers the app craches
-   Remove session from request

#2.3.1

-   Fix Tests

#2.3.0

-   Response method die no longer exists
-   Add session attribute to request

#2.2.1

-   Fix exception bug

#2.2.0

-   Implement error handling route

#2.1.1

-   Update custom data from "data" to "custom" on request

# 2.1.0

-   Ability to set custom data on the request object

# 2.0.0

-   Remove builtin Session manager

# 1.1.0

-   Ability to use any template engine (must implemet the IViewEngineServiceProvider)

# 1.0.0

-   Update Internals
