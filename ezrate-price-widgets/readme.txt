=== EZRate Price Widgets – Live Dollar, Gold, Coin & Crypto Prices ===
Contributors: aslanaria2000
Tags: exchange rates, gold price, currency, crypto prices, toman
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.3.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Live dollar, euro, gold, coin, USDT and crypto prices in Toman from Iran's free market — ticker, price bar, cards, table or chart widgets.

== Description ==

**EZRate Price Widgets** shows Iran's free-market exchange rates on your WordPress site: the US dollar, euro, UAE dirham, Turkish lira and 20+ other currencies, 18K and 24K gold, Emami and Bahar Azadi coins, the global gold ounce, USDT, Bitcoin and other crypto — in Toman or in US dollars, euros, Turkish lira, UAE dirhams or Russian rubles, in Persian, English, Arabic, Russian or Turkish.

Everything is set up from your own dashboard. No account, no API key, no code.

= Five widget types =

* **Moving ticker** — prices scroll like a news ticker (pauses on hover, respects "reduce motion").
* **Price bar** — one row of prices.
* **Price cards** — tidy cards for a sidebar or home page, 1–6 columns.
* **Price table** — name, buy, sell and 24-hour change.
* **Chart** — one asset with day / week / month / all ranges, low and high.

= Full control from WordPress =

* Pick the prices and their order (up to 20), the language (Persian, English, Arabic, Russian or Turkish; Persian and Arabic right-to-left) and a light or dark theme.
* Show the prices in Toman or in another currency your visitors know: US dollar, euro, Turkish lira, UAE dirham or Russian ruble (crypto and the gold ounce stay in dollars).
* Your own colours: background, text, secondary text, border, accent, price up / down and the chart line.
* Corner radius, text size, your theme's font or the system font.
* Show or hide the buy price, the 24-hour change, flags and icons, the update time.
* Live preview while you edit.
* **One-click placing, no code:** show it at the top or bottom of every page, above or below the text of your posts or pages, add it to your sidebar, or create a ready "Live prices" page — each with one button (on all pages, the home page, posts only or pages only; optionally hidden on phones).
* Or place it yourself with its **shortcode**, the **"EZRate price widget" block** or the **classic widget**.
* Your theme files are never changed.
* Quick shortcode without saving a widget: `[ezrate_widget type="cards" assets="usd,eur,g18,emami" lang="fa" cur="irt" theme="dark"]` (`lang`: fa, en, ar, ru, tr · `cur`: irt, usd, eur, try, aed, rub)

= Fast and light =

* Widgets are rendered on your server from a cached copy of the prices: no external scripts, no iframes, nothing slows your pages down.
* The prices are refreshed in the background every 30 minutes (the free EZRate data plan). Your visitors never wait for the data service, and if it cannot be reached the last prices stay on the page.
* About 6 KB of CSS and 1 KB of JavaScript, loaded only on pages that show a widget.

= Bilingual =

The dashboard is in English and Persian (فارسی). Each widget has its own language, independent of the site language. A complete guide in both languages is under **EZRate › Help**.

= به فارسی =

افزونهٔ **ویجت قیمت ایزی‌ریت** قیمت لحظه‌ای دلار، یورو، درهم، لیر و بیش از ۲۰ ارز دیگر، قیمت طلای ۱۸ عیار و ۲۴ عیار، قیمت سکه امامی و بهار آزادی، انس طلا، قیمت تتر و بیت‌کوین را از بازار آزاد ایران به تومان در سایت وردپرسی شما نشان می‌دهد: زیرنویس متحرک قیمت، نوار قیمت بالای سایت، کارت و جدول قیمت ارز و طلا، و نمودار. بدون ثبت‌نام، بدون کلید API و بدون یک خط کدنویسی؛ همهٔ تنظیمات در پیشخوان وردپرس، با بلوک گوتنبرگ، ابزارک و شورت‌کد.

== External services ==

This plugin gets the exchange rates from the **EZRate data service** (https://ezrate.me), operated by EZRate.

* **What is sent and when:** your server requests `https://api.ezrate.me/v1/wp/data` about every 30 minutes (WP-Cron), when the cached prices have expired, and when an administrator clicks "Refresh prices now". The request contains the list of assets shown as charts (for example `usd`) and the standard WordPress HTTP headers (your site address in the User-Agent). No data about your visitors is sent.
* **Images:** the currency flags and asset icons are loaded by your visitors' browsers from `https://ezrate.me/i/…` (for example `https://ezrate.me/i/usd.svg`). Turn icons off in a widget's settings to avoid these requests.
* **Links:** the plugin adds no links to your pages unless you allow them (the one-time question after your first widget, or **EZRate › Settings**): links from prices to their pages on ezrate.me, and a small «Source: EZRate» credit with the EZRate icon. Both are off by default.

Terms of use: https://ezrate.me/legal/terms/en — Privacy policy: https://ezrate.me/legal/privacy/en

== Installation ==

1. Install the plugin from **Plugins › Add New** (search for "EZRate") or upload the zip, then activate it.
2. Go to **EZRate › Widgets**. A first ticker is already there; click it to choose prices, language and colours, or click **Add widget**.
3. Right after activation a notice on your dashboard offers one-click buttons: **Show at the top of my whole site**, **Add to my sidebar** (classic themes: opens Appearance › Widgets, where you add "EZRate price widget") or **Create a prices page with it**. The same buttons are on every widget's page.
4. Or choose **Where should it appear?** in the widget (top / bottom of the site, above / below posts and pages), or use its shortcode, e.g. `[ezrate_widget id="1"]`, or the **EZRate price widget** block.

== Frequently Asked Questions ==

= Is it free? =

Yes. The plugin and the data it uses are free.

= How often are the prices updated? =

Every 30 minutes, the update interval of the free EZRate data plan. For live, minute-by-minute prices use the widgets of your EZRate account at https://ezrate.me/services/widgets/en.

= Where do the prices come from? =

From EZRate: the median of real trades on Iran's free market for currencies, gold and coins; crypto from major global exchanges.

= Will it slow down my site? =

No. Prices are stored in your database and the widgets are plain HTML and CSS rendered by your server. Pages never wait for the data service.

= Does it work with page caching plugins? =

Yes. Cached pages show the prices of the moment they were cached; most caching plugins refresh pages regularly.

= Does the plugin add links to my site? =

Not unless you allow it. After you create a widget the plugin asks once whether your widgets may show a small «Source: EZRate» link with its icon; you can also switch it (and links from each price to its chart page) on or off in **EZRate › Settings**. Both are off until you say yes.

= Can I show the prices in English? =

Yes, every widget has its own language: Persian, English, Arabic, Russian or Turkish (Persian and Arabic right-to-left).

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

= 2.3.1 =
* Readme: corrected the description of the optional credit link and the widget languages.

= 2.3.0 =
* New: optional «Source: EZRate» link with the EZRate icon in every widget type (ticker and price bar too), in the widget's language; asked once, off until you allow it.
* Better plugin name and description for search.

= 2.2.0 =
* New: widgets in Arabic, Russian and Turkish (besides Persian and English), with their own number and date formats; Arabic right-to-left.
* New: currency of the prices per widget — Toman, US dollar, euro, Turkish lira, UAE dirham or Russian ruble; charts and low / high follow it.
* New: default language and currency for new widgets in EZRate › Settings; `cur` attribute in the shortcode.

= 2.1.1 =
* Translations are delivered by translate.wordpress.org; corrected service links.

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

= 2.2.0 =
Widgets in Arabic, Russian and Turkish, and prices in the currency of your choice.

= 2.1.1 =
Corrected links to the EZRate terms, privacy policy and widgets page.

= 2.1.0 =
Show your widgets on the site in one click — no shortcode needed.

= 2.0.1 =
Compatibility with the WordPress 7 block editor.

= 2.0.0 =
Widgets are now configured in your WordPress dashboard. Re-create your widgets under EZRate › Widgets.
