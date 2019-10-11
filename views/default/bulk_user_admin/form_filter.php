<?php
/**
 * Filter the users
 */

$db_prefix = elgg_get_config('dbprefix');
$users = elgg_extract('users', $vars);
$domain = elgg_extract('domain', $vars);
$banned = elgg_extract('banned', $vars);
$spam = elgg_extract('spam', $vars);
$include_enqueued = elgg_extract('include_enqueued', $vars);
$options = elgg_extract('options', $vars);
$options['count'] = true;

$filter_body = '';

// banned user selection
$banned_count = bulk_user_admin_get_users(array_merge($options, ['only_banned' => true]));
$filter_body .= elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('bulk_user_admin:banned_only', [$banned_count]),
	'name' => 'banned',
	'value' => 1,
	'checked' => (bool) $banned,
]);

// spam accounts selection
$spam_count = bulk_user_admin_get_users(array_merge($options, ['spam' => true]));
$filter_body .= elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('bulk_user_admin:spam', [$spam_count]),
	'#help' => elgg_echo('bulk_user_admin:spam:help'),
	'name' => 'spam',
	'value' => 1,
	'checked' => (bool) $spam,
]);

// queued for deletion
$enqueued_count = bulk_user_admin_get_users(array_merge($options, ['enqueued' => 'only']));
$filter_body .= elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('bulk_user_admin:include_enqueued', [$enqueued_count]),
	'name' => 'include_enqueued',
	'value' => 1,
	'checked' => (bool) $include_enqueued,
]);

// filter on e-mail domain
$domain_input_options = [
	'#type' => 'text',
	'#label' => elgg_echo('bulk_user_admin:domain'),
	'#help' => elgg_echo('bulk_user_admin:domain:help'),
	'name' => 'domain',
	'value' => $domain,
	'class' => 'elgg-input-thin',
];
if (!empty($domain)) {
	$domain_count = bulk_user_admin_get_users(array_merge($options, ['domain' => $domain]));
	$domain_input_options['#label'] .= elgg_format_element('span', ['class' => 'mls'], elgg_echo('bulk_user_admin:domain_count', [$domain_count]));
}

$filter_body .= elgg_view_field($domain_input_options);

// buttons
$filter_body .= elgg_view_field([
	'#type' => 'fieldset',
	'align' => 'horizontal',
	'fields' => [
		[
			'#type' => 'submit',
			'value' => elgg_echo('update'),
		],
		[
			'#type' => 'reset',
			'value' => elgg_echo('bulk_user_admin:clear'),
			'onClick' => 'document.location.href = "/admin/users/bulk_user_admin"',
		],
	],
]);

// make form
$filter_form = elgg_view('input/form', [
	'body' => $filter_body,
	'action' => 'admin/users/bulk_user_admin',
	'method' => 'get',
	'disable_security' => true
]);

// should the form be hidden
$filter_class = 'hidden';
if ($banned || $domain || $include_enqueued || $spam) {
	$filter_class = '';
}

$filter_toggle = '';
if ($filter_class == 'hidden') {
	$filter_toggle = elgg_view('output/url', [
		'text' => elgg_echo('bulk_user_admin:add_filters'),
		'href' => '#bulk-user-admin-filter',
		'rel' => 'toggle',
		'is_trusted' => true,
		'class' => 'elgg-button elgg-button-action'
	]);
}

$legend = elgg_echo('bulk_user_admin:filters');

echo <<<HTML
$filter_toggle
<fieldset id="bulk-user-admin-filter" class="elgg-fieldset mtm $filter_class">
	<legend>$legend</legend>
	$filter_form
</fieldset>
HTML;
