CHANGELOG

3.6.3 (31 December 2017)
*	PHP7 fix: remove deprecated (unset) cast
*	Web->request: restricted follow_location to 3XX responses only
*	CLI mode: refactored arguments parsing
*	CLI mode: fixed query string encoding
*	SMTP: Refactor parsing of attachments
*	SMTP: clean-up mail headers for multipart messages, [#1065](https://github.com/bcosca/fatfree/issues/1065)
*	config: fixed performance issues on parsing config files
*	config: cast command parameters in config entries to php type & constant, [#1030](https://github.com/bcosca/fatfree/issues/1030)
*	config: reduced registry calls
*	config: skip hive escaping when resolving dynamic config vars, [#1030](https://github.com/bcosca/fatfree/issues/1030)
*	Bug fix: Incorrect cookie lifetime computation, [#1070](https://github.com/bcosca/fatfree/issues/1070), [#1016](https://github.com/bcosca/fatfree/issues/1016)
*	DB\SQL\Mapper: use RETURNING option instead of a sequence query to get lastInsertId in PostgreSQL, [#1069](https://github.com/bcosca/fatfree/issues/1069), [#230](https://github.com/bcosca/fatfree-core/issues/230)
*	DB\SQL\Session: check if _agent is too long for SQL based sessions [#236](https://github.com/bcosca/fatfree-core/issues/236)
*	DB\SQL\Session: fix Session handler table creation issue on SQL Server, [#899](https://github.com/bcosca/fatfree/issues/899)
*	DB\SQL: fix oracle db issue with empty error variable, [#1072](https://github.com/bcosca/fatfree/issues/1072)
*	DB\SQL\Mapper: fix sorting issues on SQL Server, [#1052](https://github.com/bcosca/fatfree/issues/1052) [#225](https://github.com/bcosca/fatfree-core/issues/225)
*	Prevent directory traversal attacks on filesystem based cache [#1073](https://github.com/bcosca/fatfree/issues/1073)
*	Bug fix, Template: PHP constants used in include with attribute, [#983](https://github.com/bcosca/fatfree/issues/983)
*	Bug fix, Template: Numeric value in expression alters PHP_EOL context
*	Template: use existing linefeed instead of PHP_EOL, [#1048](https://github.com/bcosca/fatfree/issues/1048)
*	Template: make newline interpolation handling configurable [#223](https://github.com/bcosca/fatfree-core/issues/223)
*	Template: add beforerender to Preview
*	fix custom FORMATS without modifiers
*	Cache: Refactor Cache->reset for XCache
*	Cache: loosen reset cache key pattern, [#1041](https://github.com/bcosca/fatfree/issues/1041)
*	XCache: suffix reset only works if xcache.admin.enable_auth is disabled
*	Added HTTP 103 as recently approved by the IETF
*	LDAP changes to for AD flexibility [#227](https://github.com/bcosca/fatfree-core/issues/227)
*	Hide debug trace from ajax errors when DEBUG=0 [#1071](https://github.com/bcosca/fatfree/issues/1071)
*	fix View->render using potentially wrong cache entry

3.6.2 (26 June 2017)
*   Return a status code > 0 when dying on error [#220](https://github.com/bcosca/fatfree-core/issues/220)
*   fix SMTP line width [#215](https://github.com/bcosca/fatfree-core/issues/215)
*   Allow using a custom field for ldap user id checking [#217](https://github.com/bcosca/fatfree-core/issues/217)
*   NEW: DB\SQL->exists: generic method to check if SQL table exists
*   Pass handler to route handler and hooks [#1035](https://github.com/bcosca/fatfree/issues/1035)
*   pass carriage return of multiline dictionary keys
*   Better Web->slug customization
*   fix incorrect header issue [#211](https://github.com/bcosca/fatfree-core/issues/211)
*   fix schema issue on databases with case-sensitive collation, fixes [#209](https://github.com/bcosca/fatfree-core/issues/209)
*   Add filter for deriving C-locale equivalent of a number
*   Bug fix: @LANGUAGE remains unchanged after override
*   abort: added Header pre-check
*   Assemble URL after ONREROUTE
*   Add reroute argument to skip script termination
*   Invoke ONREROUTE after headers are sent
*   SQLite switch to backtick as quote
*   Bug fix: Incorrect timing in SQL query logs
*   DB\SQL\Mapper: Cast return value of count to integer
*   Patched $_SERVER['REQUEST_URI'] to ensure it contains a relative URI
*   Tweak debug verbosity
*   fix php carriage return issue in preview->build [#205](https://github.com/bcosca/fatfree-core/pull/205)
*   fixed template string resolution [#205](https://github.com/bcosca/fatfree-core/pull/205)
*   Fixed unexpected default seed on CACHE set [#1028](https://github.com/bcosca/fatfree/issues/1028)
*   DB\SQL\Mapper: Optimized field escaping on options
*   Optimize template conversion to PHP file

3.6.1 (2 April 2017)
*	NEW: Recaptcha plugin [#194](https://github.com/bcosca/fatfree-core/pull/194)
*	NEW: MB variable for detecting multibyte support
*	NEW: DB\SQL: Cache parsed schema for the TTL duration
*	NEW: quick erase flag on Jig/Mongo/SQL mappers [#193](https://github.com/bcosca/fatfree-core/pull/193)
*	NEW: Allow OPTIONS method to return a response body [#171](https://github.com/bcosca/fatfree-core/pull/171)
*	NEW: Add support for Memcached (bcosca/fatfree#997)
*	NEW: Rudimentary preload resource (HTTP2 server) support via template push()
*	NEW: Add support for new MongoDB driver [#177](https://github.com/bcosca/fatfree-core/pull/177)
*	Changed: template filter are all lowercase now
*	Changed: Fix template lookup inconsistency: removed base dir from UI on render
*	Changed: count() method now has an options argument [#192](https://github.com/bcosca/fatfree-core/pull/192)
*	Changed: SMTP, Spit out error message if any
*	\DB\SQL\Mapper: refactored row count strategy
*	DB\SQL\Mapper: Allow non-scalar values to be assigned as mapper property
*	DB\SQL::PARAM_FLOAT: remove cast to float (#106 and bcosca/fatfree#984) (#191)
*	DB\SQL\mapper->erase: allow empty string
*	DB\SQL\mapper->insert: fields reset after successful INSERT
*	Add option to debounce Cursor->paginate subset [#195](https://github.com/bcosca/fatfree-core/pull/195)
*	View: Don't delete sandboxed variables (#198)
*	Preview: Optimize compilation of template expressions
*	Preview: Use shorthand tag for direct rendering
*	Preview->resolve(): new tweak to allow template persistence as option
*	Web: Expose diacritics translation table
*	SMTP: Enable logging of message body only when $log argument is 'verbose'
*	SMTP: Convert headers to camelcase for consistency
*	make cache seed more flexible, #164
*	Improve trace details for DEBUG>2
*	Enable config() to read from an array of input files
*	Improved alias and reroute regex
*	Make camelCase and snakeCase Unicode-aware
*	format: Provision for optional whitespaces
*	Break APCu-BC dependence
*	Old PHP 5.3 cleanup
*	Debug log must include HTTP query
*	Recognize X-Forwarded-Port header (bcosca/fatfree#1002)
*	Avoid use of deprecated mcrypt module
*	Return only the client's IP when using the `X-Forwarded-For` header to deduce an IP address
*	Remove orphan mutex locks on termination (#157)
*	Use 80 as default port number to avoid issues when `$_SERVER['SERVER_PORT']` is not existing
*	fread replaced with readfile() for simple send() usecase
*	Bug fix: request URI with multiple leading slashes, #203
*	Bug fix: Query generates wrong adhoc field value
*	Bug fix: SMTP stream context issue #200
*	Bug fix: child pseudo class selector in minify, bcosca/fatfree#1008
*	Bug fix: "Undefined index: CLI" error (#197)
*	Bug fix: cast Cache-Control expire time to int, bcosca/fatfree#1004
*	Bug fix: Avoid issuance of multiple Content-Type headers for nested templates
*	Bug fix: wildcard token issue with digits (bcosca/fatfree#996)
*	Bug fix: afterupdate ignored when row does not change
*	Bug fix: session handler read() method for PHP7 (need strict string) #184 #185
*	Bug fix: reroute mocking in CLI mode (#183)
*	Bug fix: Reroute authoritative relative references (#181)
*	Bug fix: locales order and charset hyphen
*	Bug fix: base stripped twice in router (#176)

3.6.0 (19 November 2016)
*	NEW: [cli] request type
*	NEW: console-friendly CLI mode
*	NEW: lexicon caching
*	NEW: Silent operator skips startup error check (#125)
*	NEW: DB\SQL->trans()
*	NEW: custom config section parser, i.e. [conf > Foo::bar]
*	NEW: support for cache tags in SQL
*	NEW: custom FORMATS
*	NEW: Mongo mapper fields whitelist
*	NEW: WebSocket server
*	NEW: Base->extend method (#158)
*	NEW: Implement framework variable caching via config, i.e. FOO = "bar" | 3600
*	NEW: Lightweight OAuth2 client
*	NEW: SEED variable, configurable app-specific hashing prefix (#149, bcosca/fatfree#951, bcosca/fatfree#884, bcosca/fatfree#629)
*	NEW: CLI variable
*	NEW: Web->send, specify custom filename (#124)
*	NEW: Web->send, added flushing flag (#131)
*	NEW: Indexed route wildcards, now exposed in PARAMS['*']
*	Changed: PHP 5.4 is now the minimum version requirement
*	Changed: Prevent database wrappers from being cloned
*	Changed: Router works on PATH instead of URI (#126) NB: PARAMS.0 no longer contains the query string
*	Changed: Removed ALIASES autobuilding (#118)
*	Changed: Route wildcards match empty strings (#119)
*	Changed: Disable default debug highlighting, HIGHLIGHT is false now
*	General PHP 5.4 optimizations
*	Optimized config parsing
*	Optimized Base->recursive
*	Optimized header extraction
*	Optimized cache/expire headers
*	Optimized session_start behaviour (bcosca/fatfree#673)
*	Optimized reroute regex
*	Tweaked cookie removal
*	Better route precedence order
*	Performance tweak: reduced cache calls
*	Refactored lexicon (LOCALES) build-up, much faster now
*	Added turkish locale bug workaround
*	Geo->tzinfo Update to UTC
*	Added Xcache reset (bcosca/fatfree#928)
*	Redis cache: allow db name in dsn
*	SMTP: Improve server emulation responses
*	SMTP: Optimize transmission envelope
*	SMTP: Implement mock transmission
*	SMTP: Various bug fixes and feature improvements
*	SMTP: quit on failed authentication
*	Geo->weather: force metric units
*	Base->until: Implement CLI interoperability
*	Base->format: looser plural syntax
*	Base->format: Force decimal as default number format
*	Base->merge: Added $keep flag to save result to the hive key
*	Base->reroute: Allow array as URL argument for aliasing
*	Base->alias: Allow query string (or array) to be appended to alias
*	Permit reroute to named routes with URL query segment
*	Sync COOKIE global on set()
*	Permit non-hive variables to use JS dot notation
*	RFC2616: Use absolute URIs for Location header
*	Matrix->calendar: Check if calendar extension is loaded
*	Markdown: require start of line/whitespace for text processing (#136)
*	DB\[SQL|Jig|Mongo]->log(FALSE) disables logging
*	DB\SQL->exec: Added timestamp toggle to db log
*	DB\SQL->schema: Remove unnecessary line terminators
*	DB\SQL\Mapper: allow array filter with empty string
*	DB\SQL\Mapper: optimized handling for key-less tables
*	DB\SQL\Mapper: added float support (#106)
*	DB\SQL\Session: increased default column sizes (#148, bcosca/fatfree#931, bcosca/fatfree#950)
*	Web: Catch cURL errors
*	Optimize Web->receive (bcosca/fatfree#930)
*	Web->minify: fix arbitrary file download vulnerability
*	Web->request: fix cache control max-age detection (bcosca/fatfree#908)
*	Web->request: Add request headers & error message to return value (bcosca/fatfree#737)
*	Web->request: Refactored response to HTTP request
*	Web->send flush while sending big files
*	Image->rgb: allow hex strings
*	Image->captcha: Check if GD module supports TrueType
*	Image->load: Return FALSE on load failure
*	Image->resize: keep aspect ratio when only width or height was given
*	Updated OpenID lib (bcosca/fatfree#965)
*	Audit->card: add new mastercard "2" BIN range (bcosca/fatfree#954)
*	Deprecated: Bcrypt class
*	Preview->render: optimized detection to remove short open PHP tags and allow xml tags (#133)
*	Display file and line number in exception handler (bcosca/fatfree#967)
*	Added error reporting level to Base->error and ERROR.level (bcosca/fatfree#957)
*	Added optional custom cache instance to Session (#141)
*	CLI-aware mock()
*	XFRAME and PACKAGE can be switched off now (#128)
*	Bug fix: wrong time calculation on memcache reset (#170)
*	Bug fix: encode CLI parameters
*	Bug fix: Close connection on abort explicitly (#162)
*	Bug fix: Image->identicon, Avoid double-size sprite rotation (and possible segfault)
*	Bug fix: Image->render and Image->dump, removed unnecessary 2nd argument (#146)
*	Bug fix: Magic->offsetset, access property as array element (#147)
*	Bug fix: multi-line custom template tag parsing (bcosca/fatfree#935)
*	Bug fix: cache headers on errors (bcosca/fatfree#885)
*	Bug fix: Web, deprecated CURLOPT_SSL_VERIFYHOST in curl
*	Bug fix: Web, Invalid user error constant (bcosca/fatfree#962)
*	Bug fix: Web->request, redirections for domain-less location (#135)
*	Bug fix: DB\SQL\Mapper, reset changed flag after update (#142, #152)
*	Bug fix: DB\SQL\Mapper, fix changed flag when using assignment operator #143 #150 #151
*	Bug fix: DB\SQL\Mapper, revival of the HAVING clause
*	Bug fix: DB\SQL\Mapper, pgsql with non-integer primary keys (bcosca/fatfree#916)
*	Bug fix: DB\SQL\Session, quote table name (bcosca/fatfree#977)
*	Bug fix: snakeCase returns word starting with underscore (bcosca/fatfree#927)
*	Bug fix: mock does not populate PATH variable
*	Bug fix: Geo->weather API key (#129)
*	Bug fix: Incorrect compilation of array element with zero index
*	Bug fix: Compilation of array construct is incorrect
*	Bug fix: Trailing slash redirection on UTF-8 paths (#121)

3.5.1 (31 December 2015)
*	NEW: ttl attribute in <include> template tag
*	NEW: allow anonymous function for template filter
*	NEW: format modifier for international and custom currency symbol
*	NEW: Image->data() returns image resource
*	NEW: extract() get prefixed array keys from an assoc array
*	NEW: Optimized and faster Template parser with full support for HTML5 empty tags
*	NEW: Added support for {@token} encapsulation syntax in routes definition
*	NEW: DB\SQL->exec(), automatically shift to 1-based query arguments
*	NEW: abort() flush output
*	Added referenced value to devoid()
*	Template token filters are now resolved within Preview->token()
*	Web->_curl: restrict redirections to HTTP
*	Web->minify(), skip importing of external files
*	Improved session and error handling in until()
*	Get the error trace array with the new $format parameter
*	Better support for unicode URLs
*	Optimized TZ detection with date_default_timezone_get()
*	format() Provide default decimal places
*	Optimize code: remove redundant TTL checks
*	Optimized timeout handling in Web->request()
*	Improved PHPDoc hints
*	Added missing russian DIACRITICS letters
*	DB\Cursor: allow child implementation of reset()
*	DB\Cursor: Copyfrom now does an internal call to set()
*	DB\SQL: Provide the ability to disable SQL logging
*	DB\SQL: improved query analysis to trigger fetchAll
*	DB\SQL\Mapper: added support for binary table columns
*	SQL,JIG,MONGO,CACHE Session handlers refactored and optimized
*	SMTP Refactoring and optimization
*	Bug fix: SMTP, Align quoted_printable_encode() with SMTP specs (dot-stuffing)
*	Bug fix: SMTP, Send buffered optional headers to output
*	Bug fix: SMTP, Content-Transfer-Encoding for non-TLS connections
*	Bug fix: SMTP, Single attachment error
*	Bug fix: Cursor->load not always mapping to first record
*	Bug fix: dry SQL mapper should not trigger 'load'
*	Bug fix: Code highlighting on empty text
*	Bug fix: Image->resize, round dimensions instead of cast
*	Bug fix: whitespace handling in $f3->compile()
*	Bug fix: TTL of `View` and `Preview` (`Template`)
*	Bug fix: token filter regex
*	Bug fix: Template, empty attributes
*	Bug fix: Preview->build() greedy regex
*	Bug fix: Web->minify() single-line comment on last line
*	Bug fix: Web->request(), follow_location with cURL and open_basedir
*	Bug fix: Web->send() Single quotes around filename not interpreted correctly by some browsers

3.5.0 (2 June 2015)
*	NEW: until() method for long polling
*	NEW: abort() to disconnect HTTP client (and continue execution)
*	NEW: SQL Mapper->required() returns TRUE if field is not nullable
*	NEW: PREMAP variable for allowing prefixes to handlers named after HTTP verbs
*	NEW: [configs] section to allow config includes
*	NEW: Test->passed() returns TRUE if no test failed
*	NEW: SQL mapper changed() function
*	NEW: fatfree-core composer support
*	NEW: constants() method to expose constants
*	NEW: Preview->filter() for configurable token filters
*	NEW: CORS variable for Cross-Origin Resource Sharing support, #731
*	Change in behavior: Switch to htmlspecialchars for escaping
*	Change in behavior: No movement in cursor position after erase(), #797
*	Change in behavior: ERROR.trace is a multiline string now
*	Change in behavior: Strict token recognition in <include> href attribute
*	Router fix: loose method search
*	Better route precedence order, #12
*	Preserve contents of ROUTES, #723
*	Alias: allow array of parameters
*	Improvements on reroute method
*	Fix for custom Jig session files
*	Audit: better mobile detection
*	Audit: add argument to test string as browser agent
*	DB mappers: abort insert/update/erase from hooks, #684
*	DB mappers: Allow array inputs in copyfrom()
*	Cache,SQL,Jig,Mongo Session: custom callback for suspect sessions
*	Fix for unexpected HIVE values when defining an empty HIVE array
*	SQL mapper: check for results from CALL and EXEC queries, #771
*	SQL mapper: consider SQL schema prefix, #820
*	SQL mapper: write to log before execution to
	enable tracking of PDOStatement error
*	Add SQL Mapper->table() to return table name
*	Allow override of the schema in SQL Mapper->schema()
*	Improvement: Keep JIG table as reference, #758
*	Expand regex to include whitespaces in SQL DB dsn, #817
*	View: Removed reserved variables $fw and $implicit
*	Add missing newlines after template expansion
*	Web->receive: fix for complex field names, #806
*	Web: Improvements in socket engine
*	Web: customizable user_agent for all engines, #822
*	SMTP: Provision for Content-ID in attachments
*	Image + minify: allow absolute paths
*	Promote framework error to E_USER_ERROR
*	Geo->weather switch to OpenWeather
*	Expose mask() and grab() methods for routing
*	Expose trace() method to expose the debug backtrace
*	Implement recursion strategy using IteratorAggregate, #714
*	Exempt whitespace between % and succeeding operator from being minified, #773
*	Optimized error detection and ONERROR handler, fatfree-core#18
*	Tweak error log output
*	Optimized If-Modified-Since cache header usage
*	Improved APCu compatibility, #724
*	Bug fix: Web::send fails on filename with spaces, #810
*	Bug fix: overwrite limit in findone()
*	Bug fix: locale-specific edge cases affecting SQL schema, #772
*	Bug fix: Newline stripping in config()
*	Bug fix: bracket delimited identifier for sybase and dblib driver
*	Bug fix: Mongo mapper collection->count driver compatibility
*	Bug fix: SQL Mapper->set() forces adhoc value if already defined
*	Bug fix: Mapper ignores HAVING clause
*	Bug fix: Constructor invocation in call()
*	Bug fix: Wrong element returned by ajax/sync request
*	Bug fix: handling of non-consecutive compound key members
*	Bug fix: Virtual fields not retrieved when group option is present, #757
*	Bug fix: group option generates incorrect SQL query, #757
*	Bug fix: ONERROR does not receive PARAMS on fatal error

3.4.0 (1 January 2015)
*	NEW: [redirects] section
*	NEW: Custom config sections
*	NEW: User-defined AUTOLOAD function
*	NEW: ONREROUTE variable
*	NEW: Provision for in-memory Jig database (#727)
*	Return run() result (#687)
*	Pass result of run() to mock() (#687)
*	Add port suffix to REALM variable
*	New attribute in <include> tag to extend hive
*	Adjust unit tests and clean up templates
*	Expose header-related methods
*	Web->request: allow content array
*	Preserve contents of ROUTES (#723)
*	Smart detection of PHP functions in template expressions
*	Add afterrender() hook to View class
*	Implement ArrayAccess and magic properties on hive
*	Improvement on mocking of superglobals and request body
*	Fix table creation for pgsql handled sessions
*	Add QUERY to hive
*	Exempt E_NOTICE from default error_reporting()
*	Add method to build alias routes from template, fixes #693
*	Fix dangerous caching of cookie values
*	Fix multiple encoding in nested templates
*	Fix node attribute parsing for empty/zero values
*	Apply URL encoding on BASE to emulate v2 behavior (#123)
*	Improve Base->map performance (#595)
*	Add simple backtrace for fatal errors
*	Count Cursor->load() results (#581)
*	Add form field name to Web->receive() callback arguments
*	Fix missing newlines after template expansion
*	Fix overwrite of ENCODING variable
*	limit & offset workaround for SQL Server, fixes #671
*	SQL Mapper->find: GROUP BY SQL compliant statement
*	Bug fix: Missing abstract method fields()
*	Bug fix: Auto escaping does not work with mapper objects (#710)
*	Bug fix: 'with' attribute in <include> tag raise error when no token
	inside
*	View rendering: optional Content-Type header
*	Bug fix: Undefined variable: cache (#705)
*	Bug fix: Routing does not work if project base path includes valid
	special URI character (#704)
*	Bug fix: Template hash collision (#702)
*	Bug fix: Property visibility is incorrect (#697)
*	Bug fix: Missing Allow header on HTTP 405 response
*	Bug fix: Double quotes in lexicon files (#681)
*	Bug fix: Space should not be mandatory in ICU pluralization format string
*	Bug fix: Incorrect log entry when SQL query contains a question mark
*	Bug fix: Error stack trace
*	Bug fix: Cookie expiration (#665)
*	Bug fix: OR operator (||) parsed incorrectly
*	Bug fix: Routing treatment of * wildcard character
*	Bug fix:  Mapper copyfrom() method doesn't allow class/object callbacks
	(#590)
*	Bug fix: exists() creates elements/properties (#591)
*	Bug fix: Wildcard in routing pattern consumes entire query string (#592)
*	Bug fix: Workaround bug in latest MongoDB driver
*	Bug fix: Default error handler silently fails for AJAX request with
	DEBUG>0 (#599)
*	Bug fix: Mocked BODY overwritten (#601)
*	Bug fix: Undefined pkey (#607)

3.3.0 (8 August 2014)
*	NEW: Attribute in <include> tag to extend hive
*	NEW: Image overlay with transparency and alignment control
*	NEW: Allow redirection of specified route patterns to a URL
*	Bug fix: Missing AND operator in SQL Server schema query (Issue #576)
*	Count Cursor->load() results (Feature request #581)
*	Mapper copyfrom() method doesn't allow class/object callbacks (Issue #590)
*	Bug fix: exists() creates elements/properties (Issue #591)
*	Bug fix: Wildcard in routing pattern consumes entire query string
	(Issue #592)
*	Tweak Base->map performance (Issue #595)
*	Bug fix: Default error handler silently fails for AJAX request with
	DEBUG>0 (Issue #599)
*	Bug fix: Mocked BODY overwritten (Issue #601)
*	Bug fix: Undefined pkey (Issue #607)
*	Bug fix: beforeupdate() position (Issue #633)
*	Bug fix: exists() return value for cached keys
*	Bug fix: Missing error code in UNLOAD handler
*	Bug fix: OR operator (||) parsed incorrectly
*	Add input name parameter to custom slug function
*	Apply URL encoding on BASE to emulate v2 behavior (Issue #123)
*	Reduce mapper update() iterations
*	Bug fix: Routing treatment of * wildcard character
*	SQL Mapper->find: GROUP BY SQL compliant statement
*	Work around bug in latest MongoDB driver
*	Work around probable race condition and optimize cache access
*	View rendering: Optional Content-Type header
*	Fix missing newlines after template expansion
*	Add form field name to Web->receive() callback arguments
*	Quick reference: add RAW variable

3.2.2 (19 March 2014)
*	NEW: Locales set automatically (Feature request #522)
*	NEW: Mapper dbtype()
*	NEW: before- and after- triggers for all mappers
*	NEW: Decode HTML5 entities if PHP>5.3 detected (Feature request #552)
*	NEW: Send credentials only if AUTH is present in the SMTP extension
	response (Feature request #545)
*	NEW: BITMASK variable to allow ENT_COMPAT override
*	NEW: Redis support for caching
*	Enable SMTP feature detection
*	Enable extended ICU custom date format (Feature request #555)
*	Enable custom time ICU format
*	Add option to turn off session table creation (Feature request #557)
*	Enhanced template token rendering and custom filters (Feature request
	#550)
*	Avert multiple loads in DB-managed sessions (Feature request #558)
*	Add EXEC to associative fetch
*	Bug fix: Building template tokens breaks on inline OR condition (Issue
	#573)
*	Bug fix: SMTP->send does not use the $log parameter (Issue #571)
*	Bug fix: Allow setting sqlsrv primary keys on insert (Issue #570)
*	Bug fix: Generated query for obtaining table schema in sqlsrv incorrect
	(Bug #565)
*	Bug fix: SQL mapper flag set even when value has not changed (Bug #562)
*	Bug fix: Add XFRAME config option (Feature request #546)
*	Bug fix: Incorrect parsing of comments (Issue #541)
*	Bug fix: Multiple Set-Cookie headers (Issue #533)
*	Bug fix: Mapper is dry after save()
*	Bug fix: Prevent infinite loop when error handler is triggered
	(Issue #361)
*	Bug fix: Mapper tweaks not passing primary keys as arguments
*	Bug fix: Zero indexes in dot-notated arrays fail to compile
*	Bug fix: Prevent GROUP clause double-escaping
*	Bug fix: Regression of zlib compression bug
*	Bug fix: Method copyto() does not include ad hoc fields
*	Check existence of OpenID mode (Issue #529)
*	Generate a 404 when a tokenized class doesn't exist
*	Fix SQLite quotes (Issue #521)
*	Bug fix: BASE is incorrect on Windows

3.2.1 (7 January 2014)
*	NEW: EMOJI variable, UTF->translate(), UTF->emojify(), and UTF->strrev()
*	Allow empty strings in config()
*	Add support for turning off php://input buffering via RAW
	(FALSE by default)
*	Add Cursor->load() and Cursor->find() TTL support
*	Support Web->receive() large file downloads via PUT
*	ONERROR safety check
*	Fix session CSRF cookie detection
*	Framework object now passed to route handler contructors
*	Allow override of DIACRITICS
*	Various code optimizations
*	Support log disabling (Issue #483)
*	Implicit mapper load() on authentication
*	Declare abstract methods for Cursor derivatives
*	Support single-quoted HTML/XML attributes (Feature request #503)
*	Relax property visibility of mappers and derivatives
*	Deprecated: {{~ ~}} instructions and {{* *}} comments; Use {~ ~} and
	{* *} instead
*	Minor fix: Audit->ipv4() return value
*	Bug fix: Backslashes in BASE not converted on Windows
*	Bug fix: UTF->substr() with negative offset and specified length
*	Bug fix: Replace named URL tokens on render()
*	Bug fix: BASE is not empty when run from document root
*	Bug fix: stringify() recursion

3.2.0 (18 December 2013)
*	NEW: Automatic CSRF protection (with IP and User-Agent checks) for
	sessions mapped to SQL-, Jig-, Mongo- and Cache-based backends
*	NEW: Named routes
*	NEW: PATH variable; returns the URL relative to BASE
*	NEW: Image->captcha() color parameters
*	NEW: Ability to access MongoCuror thru the cursor() method
*	NEW: Mapper->fields() method returns array of field names
*	NEW: Mapper onload(), oninsert(), onupdate(), and onerase() event
	listeners/triggers
*	NEW: Preview class (a lightweight template engine)
*	NEW: rel() method derives path from URL relative to BASE; useful for
	rerouting
*	NEW: PREFIX variable for prepending a string to a dictionary term;
	Enable support for prefixed dictionary arrays and .ini files (Feature
	request #440)
*	NEW: Google static map plugin
*	NEW: devoid() method
*	Introduce clean(); similar to scrub(), except that arg is passed by
	value
*	Use $ttl for cookie expiration (Issue #457)
*	Fix needs_rehash() cost comparison
*	Add pass-by-reference argument to exists() so if method returns TRUE,
	a subsequent get() is unnecessary
*	Improve MySQL support
*	Move esc(), raw(), and dupe() to View class where they more
	appropriately belong
*	Allow user-defined fields in SQL mapper constructor (Feature request
	#450)
*	Re-implement the pre-3.0 template resolve() feature
*	Remove redundant instances of session_commit()
*	Add support for input filtering in Mapper->copyfrom()
*	Prevent intrusive behavior of Mapper->copyfrom()
*	Support multiple SQL primary keys
*	Support custom tag attributes/inline tokens defined at runtime
	(Feature request #438)
*	Broader support for HTTP basic auth
*	Prohibit Jig _id clear()
*	Add support for detailed stringify() output
*	Add base directory to UI path as fallback
*	Support Test->expect() chaining
*	Support __tostring() in stringify()
*	Trigger error on invalid CAPTCHA length (Issue #458)
*	Bug fix: exists() pass-by-reference argument returns incorrect value
*	Bug fix: DB Exec does not return affected row if query contains a
	sub-SELECT (Issue #437)
*	Improve seed generator and add code for detecting of acceptable
	limits in Image->captcha() (Feature request #460)
*	Add decimal format ICU extension
*	Bug fix: 404-reported URI contains HTTP query
*	Bug fix: Data type detection in DB->schema()
*	Bug fix: TZ initialization
*	Bug fix: paginate() passes incorrect argument to count()
*	Bug fix: Incorrect query when reloading after insert()
*	Bug fix: SQL preg_match error in pdo_type matching (Issue #447)
*	Bug fix: Missing merge() function (Issue #444)
*	Bug fix: BASE misdefined in command line mode
*	Bug fix: Stringifying hive may run infinite (Issue #436)
*	Bug fix: Incomplete stringify() when DEBUG<3 (Issue #432)
*	Bug fix: Redirection of basic auth (Issue #430)
*	Bug fix: Filter only PHP code (including short tags) in templates
*	Bug fix: Markdown paragraph parser does not convert PHP code blocks
	properly
*	Bug fix: identicon() colors on same keys are randomized
*	Bug fix: quotekey() fails on aliased keys
*	Bug fix: Missing _id in Jig->find() return value
*	Bug fix: LANGUAGE/LOCALES handling
*	Bug fix: Loose comparison in stringify()

3.1.2 (5 November 2013)
*	Abandon .chm help format; Package API documentation in plain HTML;
	(Launch lib/api/index.html in your browser)
*	Deprecate BAIL in favor of HALT (default: TRUE)
*	Revert to 3.1.0 autoload behavior; Add support for lowercase folder
	names
*	Allow Spring-style HTTP method overrides
*	Add support for SQL Server-based sessions
*	Capture full X-Forwarded-For header
*	Add protection against malicious scripts; Extra check if file was really
	uploaded
*	Pass-thru page limit in return value of Cursor->paginate()
*	Optimize code: Implement single-pass escaping
*	Short circuit Jig->find() if source file is empty
*	Bug fix: PHP globals passed by reference in hive() result (Issue #424)
*	Bug fix: ZIP mime type incorrect behavior
*	Bug fix: Jig->erase() filter malfunction
*	Bug fix: Mongo->select() group
*	Bug fix: Unknown bcrypt constant

3.1.1 (13 October 2013)
*	NEW: Support OpenID attribute exchange
*	NEW: BAIL variable enables/disables continuance of execution on non-fatal
	errors
*	Deprecate BAIL in favor of HALT (default: FALSE)
*	Add support for Oracle
*	Mark cached queries in log (Feature Request #405)
*	Implement Bcrypt->needs_reshash()
*	Add entropy to SQL cache hash; Add uuid() method to DB backends
*	Find real document root; Simplify debug paths
*	Permit OpenID required fields to be declared as comma-separated string or
	array
*	Pass modified filename as argument to user-defined function in
	Web->receive()
*	Quote keys in optional SQL clauses (Issue #408)
*	Allow UNLOAD to override fatal error detection (Issue #404)
*	Mutex operator precedence error (Issue #406)
*	Bug fix: exists() malfunction (Issue #401)
*	Bug fix: Jig mapper triggers error when loading from CACHE (Issue #403)
*	Bug fix: Array index check
*	Bug fix: OpenID verified() return value
*	Bug fix: Basket->find() should return a set of results (Issue #407);
	Also implemented findone() for consistency with mappers
*	Bug fix: PostgreSQL last insert ID (Issue #410)
*	Bug fix: $port component URL overwritten by _socket()
*	Bug fix: Calculation of elapsed time

3.1.0 (20 August 2013)
*	NEW: Web->filler() returns a chunk of text from the standard
	Lorem Ipsum passage
*	Change in behavior: Drop support for JSON serialization
*	SQL->exec() now returns value of RETURNING clause
*	Add support for $ttl argument in count() (Issue #393)
*	Allow UI to be overridden by custom $path
*	Return result of PDO primitives: begintransaction(), rollback(), and
	commit()
*	Full support for PHP 5.5
*	Flush buffers only when DEBUG=0
*	Support class->method, class::method, and lambda functions as
	Web->basic() arguments
*	Commit session on Basket->save()
*	Optional enlargement in Image->resize()
*	Support authentication on hosts running PHP-CGI
*	Change visibility level of Cache properties
*	Prevent ONERROR recursion
*	Work around Apache pre-2.4 VirtualDocumentRoot bug
*	Prioritize cURL in HTTP engine detection
*	Bug fix: Minify tricky JS
*	Bug fix: desktop() detection
*	Bug fix: Double-slash on TEMP-relative path
*	Bug fix: Cursor mapping of first() and last() records
*	Bug fix: Premature end of Web->receive() on multiple files
*	Bug fix: German umlaute to its corresponding grammatically-correct
	equivalent

3.0.9 (12 June 2013)
*	NEW: Web->whois()
*	NEW: Template <switch> <case> tags
*	Improve CACHE consistency
*	Case-insensitive MIME type detection
*	Support pre-PHP 5.3.4 in Prefab->instance()
*	Refactor isdesktop() and ismobile(); Add isbot()
*	Add support for Markdown strike-through
*	Work around ODBC's lack of quote() support
*	Remove useless Prefab destructor
*	Support multiple cache instances
*	Bug fix: Underscores in OpenId keys mangled
*	Refactor format()
*	Numerous tweaks
*	Bug fix: MongoId object not preserved
*	Bug fix: Double-quotes included in lexicon() string (Issue #341)
*	Bug fix: UTF-8 formatting mangled on Windows (Issue #342)
*	Bug fix: Cache->load() error when CACHE is FALSE (Issue #344)
*	Bug fix: send() ternary expression
*	Bug fix: Country code constants

3.0.8 (17 May 2013)
*	NEW: Bcrypt lightweight hashing library\
*	Return total number of records in superset in Cursor->paginate()
*	ONERROR short-circuit (Enhancement #334)
*	Apply quotes/backticks on DB identifiers
*	Allow enabling/disabling of SQL log
*	Normalize glob() behavior (Issue #330)
*	Bug fix: mbstring 2-byte text truncation (Issue #325)
*	Bug fix: Unsupported operand types (Issue #324)

3.0.7 (2 May 2013)
*	NEW: route() now allows an array of routing patterns as first argument;
	support array as first argument of map()
*	NEW: entropy() for calculating password strength (NIST 800-63)
*	NEW: AGENT variable containing auto-detected HTTP user agent string
*	NEW: ismobile() and isdesktop() methods
*	NEW: Prefab class and descendants now accept constructor arguments
*	Change in behavior: Cache->exists() now returns timestamp and TTL of
	cache entry or FALSE if not found (Feature request #315)
*	Preserve timestamp and TTL when updating cache entry (Feature request
	#316)
*	Improved currency formatting with C99 compliance
*	Suppress unnecessary program halt at startup caused by misconfigured
	server
*	Add support for dashes in custom attribute names in templates
*	Bug fix: Routing precedene (Issue #313)
*	Bug fix: Remove Jig _id element from document property
*	Bug fix: Web->rss() error when not enough items in the feed (Issue #299)
*	Bug fix: Web engine fallback (Issue #300)
*	Bug fix: <strong> and <em> formatting
*	Bug fix: Text rendering of text with trailing punctuation (Issue #303)
*	Bug fix: Incorrect regex in SMTP

3.0.6 (31 Mar 2013)
*	NEW: Image->crop()
*	Modify documentation blocks for PHPDoc interoperability
*	Allow user to control whether Base->rerouet() uses a permanent or
	temporary redirect
*	Allow JAR elements to be set individually
*	Refactor DB\SQL\Mapper->insert() to cope with autoincrement fields
*	Trigger error when captcha() font is missing
*	Remove unnecessary markdown regex recursion
*	Check for scalars instead of DB\SQL strings
*	Implement more comprehensive diacritics table
*	Add option for disabling 401 errors when basic auth() fails
*	Add markdown syntax highlighting for Apache configuration
*	Markdown->render() deprecated to remove dependency on UI variable;
	Feature replaced by Markdown->convert() to enable translation from
	markdown string to HTML
*	Optimize factory() code of all data mappers
*	Apply backticks on MySQL table names
*	Bug fix: Routing failure when directory path contains a tilde (Issue #291)
*	Bug fix: Incorrect markdown parsing of strong/em sequences and inline HTML
*	Bug fix: Cached page not echoed (Issue #278)
*	Bug fix: Object properties not escaped when rendering
*	Bug fix: OpenID error response ignored
*	Bug fix: memcache_get_extended_stats() timeout
*	Bug fix: Base->set() doesn't pass TTL to Cache->set()
*	Bug fix: Base->scrub() ignores pass-thru * argument (Issue #274)

3.0.5 (16 Feb 2013)
*	NEW: Markdown class with PHP, HTML, and .ini syntax highlighting support
*	NEW: Options for caching of select() and find() results
*	NEW: Web->acceptable()
*	Add send() argument for forcing downloads
*	Provide read() option for applying Unix LF as standard line ending
*	Bypass lexicon() call if LANGUAGE is undefined
*	Load fallback language dictionary if LANGUAGE is undefined
*	map() now checks existence of class/methods for non-tokenized URLs
*	Improve error reporting of non-existent Template methods
*	Address output buffer issues on some servers
*	Bug fix: Setting DEBUG to 0 won't suppress the stack trace when the
	content type is application/json (Issue #257)
*	Bug fix: Image dump/render additional arguments shifted
*	Bug fix: ob_clean() causes buffer issues with zlib compression
*	Bug fix: minify() fails when commenting CSS @ rules (Issue #251)
*	Bug fix: Handling of commas inside quoted strings
*	Bug fix: Glitch in stringify() handling of closures
*	Bug fix: dry() in mappers returns TRUE despite being hydrated by
	factory() (Issue #265)
*	Bug fix: expect() not handling flags correctly
*	Bug fix: weather() fails when server is unreachable

3.0.4 (29 Jan 2013)
*	NEW: Support for ICU/CLDR pluralization
*	NEW: User-defined FALLBACK language
*	NEW: minify() now recognizes CSS @import directives
*	NEW: UTF->bom() returns byte order mark for UTF-8 encoding
*	Expose SQL\Mapper->schema()
*	Change in behavior: Send error response as JSON string if AJAX request is
	detected
*	Deprecated: afind*() methods
*	Discard output buffer in favor of debug output
*	Make _id available to Jig queries
*	Magic class now implements ArrayAccess
*	Abort execution on startup errors
*	Suppress stack trace on DEBUG level 0
*	Allow single = as equality operator in Jig query expressions
*	Abort OpenID discovery if Web->request() fails
*	Mimic PHP *RECURSION* in stringify()
*	Modify Jig parser to allow wildcard-search using preg_match()
*	Abort execution after error() execution
*	Concatenate cached/uncached minify() iterations; Prevent spillover
	caching of previous minify() result
*	Work around obscure PHP session id regeneration bug
*	Revise algorithm for Jig filter involving undefined fields (Issue #230)
*	Use checkdnsrr() instead of gethostbyname() in DNSBL check
*	Auto-adjust pagination to cursor boundaries
*	Add Romanian diacritics
*	Bug fix: Root namespace reference and sorting with undefined Jig fields
*	Bug fix: Greedy receive() regex
*	Bug fix: Default LANGUAGE always 'en'
*	Bug fix: minify() hammers cache backend
*	Bug fix: Previous values of primary keys not saved during factory()
	instantiation
*	Bug fix: Jig find() fails when search key is not present in all records
*	Bug fix: Jig SORT_DESC (Issue #233)
*	Bug fix: Error reporting (Issue #225)
*	Bug fix: language() return value

3.0.3 (29 Dec 2013)
*	NEW: [ajax] and [sync] routing pattern modifiers
*	NEW: Basket class (session-based pseudo-mapper, shopping cart, etc.)
*	NEW: Test->message() method
*	NEW: DB profiling via DB->log()
*	NEW: Matrix->calendar()
*	NEW: Audit->card() and Audit->mod10() for credit card verification
*	NEW: Geo->weather()
*	NEW: Base->relay() accepts comma-separated callbacks; but unlike
	Base->chain(), result of previous callback becomes argument of the next
*	Numerous performance tweaks
*	Interoperability with new MongoClient class
*	Web->request() now recognizes gzip and deflate encoding
*	Differences in behavior of Web->request() engines rectified
*	mutex() now uses an ID as argument (instead of filename to make it clear
	that specified file is not the target being locked, but a primitive
	cross-platform semaphore)
*	DB\SQL\Mapper field _id now returned even in the absence of any
	auto-increment field
*	Magic class spinned off as a separate file
*	ISO 3166-1 alpha-2 table updated
*	Apache redirect emulation for PHP 5.4 CLI server mode
*	Framework instance now passed as argument to any user-defined shutdown
	function
*	Cache engine now used as storage for Web->minify() output
*	Flag added for enabling/disabling Image class filter history
*	Bug fix: Trailing routing token consumes HTTP query
*	Bug fix: LANGUAGE spills over to LOCALES setting
*	Bug fix: Inconsistent dry() return value
*	Bug fix: URL-decoding

3.0.2 (23 Dec 2013)
*	NEW: Syntax-highlighted stack traces via Base->highlight(); boolean
	HIGHLIGHT global variable can be used to enable/disable this feature
*	NEW: Template engine <ignore> tag
*	NEW: Image->captcha()
*	NEW: DNSBL-based spammer detection (ported from 2.x)
*	NEW: paginate(), first(), and last() methods for data mappers
*	NEW: X-HTTP-Method-Override header now recognized
*	NEW: Base->chain() method for executing callbacks in succession
*	NEW: HOST global variable; derived from either $_SERVER['SERVER_NAME'] or
	gethostname()
*	NEW: REALM global variable representing full canonical URI
*	NEW: Auth plug-in
*	NEW: Pingback plug-in (implements both Pingback 1.0 protocol client and
	server)
*	NEW: DEBUG verbosity can now reach up to level 3; Base->stringify() drills
	down to object properties at this setting
*	NEW: HTTP PATCH method added to recognized HTTP ReST methods
*	Web->slug() now trims trailing dashes
*	Web->request() now allows relative local URLs as argument
*	Use of PARAMS in route handlers now unnecessary; framework now passes two
	arguments to route handlers: the framework object instance and an array
	containing the captured values of tokens in route patterns
*	Standardized timeout settings among Web->request() backends
*	Session IDs regenerated for additional security
*	Automatic HTTP 404 responses by Base->call() now restricted to route
	handlers
*	Empty comments in ini-style files now parsed properly
*	Use file_get_contents() in methods that don't involve high concurrency

3.0.1 (14 Dec 2013)
*	Major rewrite of much of the framework's core features
