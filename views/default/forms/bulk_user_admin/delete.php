<?php
/**
 * Form to delete users
 */



$db_prefix = elgg_get_config('dbprefix');
$users = elgg_extract('users', $vars);
$domain = elgg_extract('domain', $vars);
$only_banned = elgg_extract('banned', $vars);
$include_enqueued = elgg_extract('include_enqueued', $vars);

// profile fields
$fields = elgg_get_config('profile_fields');

?>

<table class="elgg-table bulk-user-admin-users">
	<tr>
		<th><input type="checkbox" id="checkAll"></th>
		<th><?php echo elgg_echo('bulk_user_admin:usericon');?></th>
		<th><?php echo elgg_echo('bulk_user_admin:userinfo');?><br /><?php echo elgg_echo('bulk_user_admin:profileinfo');?></th>
		<th><?php echo elgg_echo('bulk_user_admin:email');?></th>
		<th><?php echo elgg_echo('bulk_user_admin:timecreated');?><br /><?php echo elgg_echo('bulk_user_admin:lastlogin');?></th>
		<th><?php echo elgg_echo('bulk_user_admin:lastaction');?></th>
		<th><?php echo elgg_echo('bulk_user_admin:contentcounts');?></th>
	</tr>

<?php
	foreach ($users as $user) {
		$checkbox = elgg_view('input/checkbox', array(
			'name' => 'bulk_user_admin_guids[]',
			'value' => $user->guid,
			'default' => false,
			'id' => 'elgg-user-' . $user->guid
		));

		$user_icon = elgg_view_entity_icon($user, 'tiny');

		foreach (array('time_created', 'last_login', 'last_action') as $ts_name) {
			$ts = $user->$ts_name;
			if ($ts) {
				${$ts_name} = elgg_view_friendly_time($ts);
			} else {
				${$ts_name} = elgg_echo('bulk_user_admin:notavailable');
			}
		}

		$object_count = 0;
		$object_title = '';
		$content_stats = get_entity_statistics($user->guid);
		if (!empty($content_stats)) {
			foreach ($content_stats as $type => $subtypes) {
				if (empty($subtypes)) {
					continue;
				}
				
				foreach ($subtypes as $subtype => $count) {
					$object_count += (int) $count;
					
					$label = "{$type}:{$subtype}";
					if (elgg_language_key_exists("item:{$type}:{$subtype}")) {
						$label = elgg_echo("item:{$type}:{$subtype}");
					} elseif (elgg_language_key_exists("item:{$type}")) {
						$label = elgg_echo("item:{$type}");
					}
					
					$object_title .= "{$label}: {$count}\n";
				}
			}
		}
		
		$object_count = elgg_format_element('acronym', ['title' => $object_title], elgg_echo('bulk_user_admin:objectcounts') .  $object_count);

		$annotation_count = elgg_get_annotations([
			'owner_guid' => $user->guid,
			'count' => true,
		]);
		$annotation_count = elgg_echo('bulk_user_admin:annotationscounts') . $annotation_count;

		$metadata_count = elgg_get_metadata([
			'owner_guid' => $user->guid,
			'count' => true,
		]);
		$metadata_count = elgg_echo('bulk_user_admin:metadatacounts') . $metadata_count;

		$tr_class = '';

		$banned = '';
		if ($user->isBanned()) {
			$tr_class .= 'bulk-user-admin-banned';
			$banned = '<br />' . elgg_echo('bulk_user_admin:banned') . $user->ban_reason;
		}

		$enqueued = '';
		if ($user->bulk_user_admin_delete_queued) {
			$tr_class .= ' bulk-user-admin-enqueued';
			$enqueued = '<br />' . elgg_echo('bulk_user_admin:enqueued');
		}

		$profile_field_tmp = array();

		foreach (array_keys($fields) as $md_name) {
			$value = $user->$md_name;

			if (empty($value)) {
				continue;
			}
			
			if (is_array($value)) {
				$value = implode(', ', $value);
			}
			$value_short = elgg_get_excerpt($value, 75);

			$profile_field_tmp[] = elgg_echo('profile:' . $md_name) . ': '
				. elgg_format_element('acronym', ['title' => strip_tags(htmlentities($value))], $value_short);
		}

		$profile_fields = implode("<br />", $profile_field_tmp);
		if ($profile_fields) {
			$profile_fields = "<br />$profile_fields";
		}

		$email = '';
		if ($user->email) {
			list($email_username, $email_domain) = explode('@', $user->email);
			$href = elgg_http_add_url_query_elements(elgg_normalize_url('/admin/users/bulk_user_admin'),
				[
					'domain' => $email_domain,
					'banned' => $only_banned,
					'include_enqueued' => $include_enqueued
				]);
			$email = $email_username . '@' . elgg_view('output/url', [
				'text' => $email_domain,
				'href' => $href,
				'class' => 'bulk-user-admin-email-domain',
				'is_trusted' => true
			]);
		}

echo <<<___HTML
	<tr class="$tr_class">
		<td><label for="elgg-user-$user->guid">$checkbox</label></td>
		<td><label for="elgg-user-$user->guid">$user_icon</label></td>
		<td><label for="elgg-user-$user->guid">$user->name ($user->username, $user->guid) $enqueued $banned $profile_fields</label></td>
		<td>$email</td>
		<td><label for="elgg-user-$user->guid">$time_created<br />$last_login</label></td>
		<td><label for="elgg-user-$user->guid">$last_action</label></td>
		<td><label for="elgg-user-$user->guid">$object_count<br />$annotation_count<br />$metadata_count</label></td>
	</tr>
___HTML;
	}
?>

</table>

<?php

if ($domain) {
	echo elgg_view('input/hidden', [
		'name' => 'domain',
		'value' => $domain
	]);
}

if ($only_banned) {
	echo elgg_view('input/hidden', [
		'name' => 'banned',
		'value' => $only_banned
	]);
}

if ($include_enqueued) {
	echo elgg_view('input/hidden', [
		'name' => 'include_enqueued',
		'value' => $include_enqueued
	]);
}
echo elgg_view('input/submit', array(
	'value' => elgg_echo('bulk_user_admin:delete:checked'),
	'class' => 'mtm elgg-button elgg-button-submit',
	'data-confirm' => elgg_echo('bulk_user_admin:delete:checked?')
));
