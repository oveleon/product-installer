# Product installer
With this extension, products from Oveleon can be registered, managed and installed.

#### Known import limitations
- Insert tags within custom elements currently only support page references (`{link::*}})`.
- Currently only single file connections within custom elements are supported (`singleSRC`)

#### ToDo
- Docs
- Validators:
  - settings -> icons 
  - user-group connections
  - imageUrl, url (insert tags and other connection)
  - more unknown...
- Use Download Process for Packages in Manager Process
- Create product update process (need server-side product management)
- Optimize styles in Firefox and mobile (Firefox does not support the `:has()` selector)
- Provide all dependencies for manual installation
- Create database and migration process
- Use translations everywhere

- Set bundle license
- Set bundle to public
