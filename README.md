# What does it do

The minifier middleware is aware of your HTML output of TYPO3 and removes unneeded characters.

These can be configured by extension configuration.

# install

`composer req pluswerk/minify`

# Disabling minification

Set the `disabled` extension configuration option to `1` to switch minification
off completely. The default is `0` (minification active).

This is handy for environment-specific behaviour. To keep the HTML readable in
development while leaving it active everywhere else, set it per context in
`config/system/additional.php`:

```php
use TYPO3\CMS\Core\Core\Environment;

if (Environment::getContext()->isDevelopment()) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['minify']['disabled'] = '1';
}
```

# Benefits
- Less Data to transfer
- Nice looking code
- voku/html-min used by this extension is resource efficient
