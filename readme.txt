=== EZRate Price Widgets ===
Contributors: ezrate
Tags: currency, exchange rate, gold price, toman, ticker
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Live Iranian free-market rates in Toman — US dollar, euro, gold, coins, USDT and crypto — as a ticker, price bar, cards, table or chart.

== Description ==

**EZRate Price Widgets** shows Iran's free-market exchange rates on your WordPress site: the US dollar, euro, UAE dirham, Turkish lira and 20+ other currencies, 18K and 24K gold, Emami and Bahar Azadi coins, the global gold ounce, USDT, Bitcoin and other crypto — in Toman, in Persian or English.

Everything is set up from your own dashboard. No account, no API key, no code.

= Five widget types =

* **Moving ticker** — prices scroll like a news ticker (pauses on hover, respects "reduce motion").
* **Price bar** — one row of prices.
* **Price cards** — tidy cards for a sidebar or home page, 1–6 columns.
* **Price table** — name, buy, sell and 24-hour change.
* **Chart** — one asset with day / week / month / all ranges, low and high.

= Full control from WordPress =

* Pick the prices and their order (up to 20), the language (Persian RTL or English) and a light or dark theme.
* Your own colours: background, text, secondary text, border, accent, price up / down and the chart line.
* Corner radius, text size, your theme's font or the system font.
* Show or hide the buy price, the 24-hour change, flags and icons, the update time.
* Live preview while you edit.
* **One-click placing, no code:** show it at the top or bottom of every page, above or below the text of your posts or pages, add it to your sidebar, or create a ready "Live prices" page — each with one button (on all pages, the home page, posts only or pages only; optionally hidden on phones).
* Or place it yourself with its **shortcode**, the **"EZRate price widget" block** or the **classic widget**.
* Your theme files are never changed.
* Quick shortcode without saving a widget: `[ezrate_widget type="cards" assets="usd,eur,g18,emami" lang="fa" theme="dark"]`

= Fast and light =

* Widgets are rendered on your server from a cached copy of the prices: no external scripts, no iframes, nothing slows your pages down.
* The prices are refreshed in the background every 30 minutes (the free EZRate data plan). Your visitors never wait for the data service, and if it cannot be reached the last prices stay on the page.
* About 6 KB of CSS and 1 KB of JavaScript, loaded only on pages that show a widget.

= Bilingual =

The dashboard is in English and Persian (فارسی). Each widget has its own language, independent of the site language. A complete guide in both languages is under **EZRate › Help**.

== External services ==

This plugin gets the exchange rates from the **EZRate data service** (https://ezrate.me), operated by EZRate.

* **What is sent and when:** your server requests `https://api.ezrate.me/v1/wp/data` about every 30 minutes (WP-Cron), when the cached prices have expired, and when an administrator clicks "Refresh prices now". The request contains the list of assets shown as charts (for example `usd`) and the standard WordPress HTTP headers (your site address in the User-Agent). No data about your visitors is sent.
* **Images:** the currency flags and asset icons are loaded by your visitors' browsers from `https://ezrate.me/i/…` (for example `https://ezrate.me/i/usd.svg`). Turn icons off in a widget's settings to avoid these requests.
* **Links:** the plugin adds no links to your pages unless you turn them on under **EZRate › Settings** (links from prices to their pages on ezrate.me, and a small "Rates by EZRate" credit). Both are off by default.

Terms of use: https://ezrate.me/legal/terms — Privacy policy: https://ezrate.me/legal/privacy

== Installation ==

1. Install the plugin from **Plugins › Add New** (search for "EZRate") or upload the zip, then activate it.
2. Go to **EZRate › Widgets**. A first ticker is already there; click it to choose prices, language and colours, or click **Add widget**.
3. Right after activation a notice on your dashboard offers one-click buttons: **Show at the top of my whole site**, **Add to my sidebar** (classic themes) or **Create a prices page with it**. The same buttons are on every widget's page.
4. Or choose **Where should it appear?** in the widget (top / bottom of the site, above / below posts and pages), or use its shortcode, e.g. `[ezrate_widget id="1"]`, or the **EZRate price widget** block.

== Frequently Asked Questions ==

= Is it free? =

Yes. The plugin and the data it uses are free.

= How often are the prices updated? =

Every 30 minutes, the update interval of the free EZRate data plan. For live, minute-by-minute prices use the widgets of your EZRate account at https://ezrate.me/services/widgets.

= Where do the prices come from? =

From EZRate: the median of real trades on Iran's free market for currencies, gold and coins; crypto from major global exchanges.

= Will it slow down my site? =

No. Prices are stored in your database and the widgets are plain HTML and CSS rendered by your server. Pages never wait for the data service.

= Does it work with page caching plugins? =

Yes. Cached pages show the prices of the moment they were cached; most caching plugins refresh pages regularly.

= Can I show the prices in English? =

Yes, every widget has its own language: Persian (right-to-left, Persian digits) or English.

= How do I change the colours? =

Edit the widget under **EZRate › Widgets** and set the colours in the "Style" box. Leave a colour empty to use the light or dark theme colour.

= The prices are not shown =

Open **EZRate › Settings**: it shows when the prices were last downloaded and any error. Your server must be able to reach https://api.ezrate.me over HTTPS. Click "Refresh prices now" to try again.

== Screenshots ==

1. Widgets on a Persian site: fixed ticker at the top, price cards and a dollar chart.
2. Price table and a dark price bar in English.
3. Editing a widget: type, prices, display and style options with a live preview.
4. Colours of a chart widget.
5. The widget list with shortcodes.
6. Settings: data status and links.
7. The "EZRate price widget" block in the editor.

== Changelog ==

= 2.1.0 =
* New: one-click placing — show a widget at the top of the site or above all posts, add it to the sidebar, or create a prices page, without code.
* New: "above / below the text of posts and pages" placement for every widget type, on all pages, posts only, pages only or the home page.
* New: a short welcome notice after activation with the one-click buttons (dismissible).

= 2.0.1 =
* The block uses Block API version 3 (works in the iframed editor of WordPress 7).
* The widget styles load inside the editor iframe as well as on the site.
* Tested up to WordPress 7.1.

= 2.0.0 =
* New: everything is configured in WordPress — five widget types, prices and order, language, theme and custom colours, live preview.
* New: price table widget; fixed top / bottom bars; block, shortcode and classic widget.
* New: server-side rendering from cached prices (no external scripts), background refresh every 30 minutes.
* New: Persian translation and a bilingual guide.

= 1.1.0 =
* Gutenberg block with live preview.

== Upgrade Notice ==

= 2.1.0 =
Show your widgets on the site in one click — no shortcode needed.

= 2.0.1 =
Compatibility with the WordPress 7 block editor.

= 2.0.0 =
Widgets are now configured in your WordPress dashboard. Re-create your widgets under EZRate › Widgets.
