/* Migrations */

INSERT INTO public.migration (version, apply_time) VALUES
  ('m000000_000000_base', 1494996737),
  ('m130524_201442_init', 1494996822),
  ('m150227_114524_init', 1495179377),
  ('m161109_104201_rename_setting_table', 1495179377),
  ('m170323_102933_add_description_column_to_setting_table', 1495179377),
  ('m170413_125133_rename_date_columns', 1495179377);


/* Users */

INSERT INTO public."user" (id, username, auth_key, password_hash, password_reset_token, email, status, created_at, updated_at, access_token) VALUES
  (1, 'admin', 'jqZC6CgnU5unjwrMkrj-GFeAAlOj60-C', '$2y$13$kAJ3oSIXOLatMxXYXnu8aegAuR57uCOpHZXGWz4OJjZCOT9q4l43u', null, 'admin@currencies.local', 10, 1494997412, 1494997412, '12345');


/* Settings */

INSERT INTO public.setting (id, type, section, key, value, status, created_at, updated_at, description) VALUES
  (4, 'string', 'SettingsForm', 'rates_request_url', 'http://www.cbr.ru/scripts/XML_daily.asp', 1, 1495189519, 1495189519, null),
  (5, 'string', 'SettingsForm', 'frequency_success_hrs', '4', 1, 1495189519, 1495189519, null),
  (6, 'string', 'SettingsForm', 'frequency_fail_hrs', '0.3', 1, 1495189519, 1495189519, null),
  (7, 'string', 'SettingsForm', 'lifetime_hrs', '24', 1, 1495189519, 1495189519, null),
  (9, 'string', 'SettingsForm', 'currencies_request_url', 'http://www.cbr.ru/scripts/XML_valFull.asp', 1, 1495192860, 1495192860, null),
  (12, 'integer', 'LatestRatesRequestInfo', 'latest_fails_count', '0', 1, 1495382385, 1495383330, null),
  (11, 'integer', 'LatestRatesRequestInfo', 'latest_is_successful', '0', 1, 1495213836, 1495442839, null),
  (10, 'integer', 'LatestRatesRequestInfo', 'latest_time', '1495444143', 1, 1495212768, 1495444143, null);


/* Currencies */

INSERT INTO public.currencies (id, cb_id, iso_num_code, iso_char_code, name, en_name, rate_divergence_pct, nominal) VALUES
  (617, 'R01235', 840, 'USD', 'Доллар США', 'US Dollar', 20, 1),
  (623, 'R01010', 36, 'AUD', 'Австралийский доллар', 'Australian Dollar', 0, 1),
  (626, 'R01035', 826, 'GBP', 'Фунт стерлингов Соединенного королевства', 'British Pound Sterling', 0, 1),
  (634, 'R01200', 344, 'HKD', 'Гонконгский доллар', 'Hong Kong Dollar', 0, 10),
  (637, 'R01239', 978, 'EUR', 'Евро', 'Euro', 0, 1),
  (675, 'R01820', 392, 'JPY', 'Японская иена', 'Japanese Yen', 0, 100);


/* Rates Updates */

INSERT INTO public.rates_updates (id, requested_at, rate_date, success) VALUES
  (40, '2017-05-20 03:27:35.000000', '2017-05-13', true),
  (41, '2017-05-20 03:32:11.000000', '2017-05-16', false),
  (42, '2017-05-20 03:33:36.000000', '2017-05-17', true),
  (43, '2017-05-20 03:33:54.000000', '2017-05-18', true),
  (44, '2017-05-20 03:34:53.000000', '2017-05-19', true),
  (45, '2017-05-20 03:39:13.000000', '2017-05-20', true),
  (46, '2017-05-21 09:59:43.701000', '2017-05-21', false),
  (47, '2017-05-22 02:03:41.000000', '2017-05-12', false),
  (48, '2017-05-22 02:05:42.000000', '2017-05-12', false),
  (49, '2017-05-22 02:06:29.000000', '2017-05-12', false),
  (50, '2017-05-22 02:07:28.000000', '2017-05-12', false),
  (51, '2017-05-22 02:08:23.000000', '2017-05-12', false),
  (52, '2017-05-22 02:10:01.000000', '2017-05-12', false),
  (53, '2017-05-22 02:10:33.000000', '2017-05-12', false),
  (54, '2017-05-22 02:13:47.000000', '2017-05-12', false),
  (55, '2017-05-22 02:15:29.000000', '2017-05-12', true);


/* Rates */

INSERT INTO public.rates (currency_id, update_id, cb_value, value, id) VALUES
  (623, 40, 42.2728, 42.2728, 588),
  (626, 40, 73.5358, 73.5358, 589),
  (634, 40, 73.3577, 73.3577, 590),
  (617, 40, 57.1640, 57.1640, 591),
  (637, 40, 62.0915, 62.0915, 592),
  (675, 40, 50.2695, 50.2695, 593),
  (623, 42, 41.7451, 41.7451, 594),
  (626, 42, 72.8233, 72.8233, 595),
  (634, 42, 72.2527, 72.2527, 596),
  (617, 42, 56.2603, 56.2603, 597),
  (637, 42, 62.0382, 62.0382, 598),
  (675, 42, 49.5576, 49.5576, 599),
  (623, 43, 42.0204, 42.0204, 600),
  (626, 43, 73.3910, 73.3910, 601),
  (634, 43, 72.8535, 72.8535, 602),
  (617, 43, 56.7383, 56.7383, 603),
  (637, 43, 62.9568, 62.9568, 604),
  (675, 43, 50.4408, 50.4408, 605),
  (623, 44, 42.7449, 42.7449, 606),
  (626, 44, 74.6111, 74.6111, 607),
  (634, 44, 73.8231, 73.8231, 608),
  (617, 44, 57.4683, 57.4683, 609),
  (637, 44, 63.9967, 63.9967, 610),
  (675, 44, 51.8129, 51.8129, 611),
  (623, 45, 42.5386, 42.5386, 612),
  (626, 45, 74.2568, 74.2568, 613),
  (634, 45, 73.4556, 73.4556, 614),
  (617, 45, 57.1602, 68.5922, 615),
  (637, 45, 63.6479, 63.6479, 616),
  (675, 45, 51.2901, 51.2901, 617),
  (623, 55, 42.1117, 42.1117, 618),
  (626, 55, 73.8968, 73.8968, 619),
  (634, 55, 73.3301, 73.3301, 620),
  (617, 55, 57.1161, 68.5393, 621),
  (637, 55, 62.1595, 62.1595, 622),
  (675, 55, 50.0075, 50.0075, 623);
