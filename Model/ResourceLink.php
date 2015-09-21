<?php
App::uses('LtiAppModel', 'Lti.Model');

class ResourceLink extends LtiAppModel {

	public $actsAs = ['Containable'];

###
###  LTI_Resource_Link methods
###

###
#    Load the resource link from the database
###
	public function Resource_Link_load($resource_link) {

		$key = $resource_link->getKey();
		$id = $resource_link->getId();
		$sql = 'SELECT consumer_key, context_id, lti_context_id, lti_resource_id, title, settings, ' .
					 'primary_consumer_key, primary_context_id, share_approved, created, modified ' .
					 'FROM ' .$this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' ' .
					 'WHERE (consumer_key = :key) AND (context_id = :id)';
		$query = $this->db->prepare($sql);
		$query->bindValue('key', $key, PDO::PARAM_STR);
		$query->bindValue('id', $id, PDO::PARAM_STR);
		$ok = $query->execute();
		if ($ok) {
			$row = $query->fetch(PDO::FETCH_ASSOC);
			$ok = ($row !== FALSE);
		}

		if ($ok) {
			$row = array_change_key_case($row);
			$resource_link->lti_context_id = $row['lti_context_id'];
			$resource_link->lti_resource_link_id = $row['lti_resource_id'];
			$resource_link->title = $row['title'];
			if (is_string($row['settings'])) {
				$resource_link->settings = json_decode($row['settings'], TRUE);
				if (!is_array($resource_link->settings)) {
					$resource_link->settings = unserialize($row['settings']);  // check for old serialized setting
				}
				if (!is_array($resource_link->settings)) {
					$resource_link->settings = array();
				}
			} else {
				$resource_link->settings = array();
			}
			$resource_link->primary_consumer_key = $row['primary_consumer_key'];
			$resource_link->primary_resource_link_id = $row['primary_context_id'];
			$resource_link->share_approved = (is_null($row['share_approved'])) ? NULL : ($row['share_approved'] == 1);
			$resource_link->created = strtotime($row['created']);
			$resource_link->modified = strtotime($row['modified']);
		}

		return $ok;

	}

###
#    Save the resource link to the database
###
	public function Resource_Link_save($resource_link) {

		$time = time();
		$now = date("{$this->date_format} {$this->time_format}", $time);
		$settingsValue = json_encode($resource_link->settings);
		$key = $resource_link->getKey();
		$id = $resource_link->getId();
		$previous_id = $resource_link->getId(TRUE);
		if (is_null($resource_link->created)) {
			$sql = 'INSERT INTO ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' ' .
						 '(consumer_key, context_id, lti_context_id, lti_resource_id, title, settings, ' .
						 'primary_consumer_key, primary_context_id, share_approved, created, modified) ' .
						 'VALUES (:key, :id, :lti_context_id, :lti_resource_id, :title, :settings, ' .
						 ':primary_consumer_key, :primary_context_id, :share_approved, :created, :modified)';
			$query = $this->db->prepare($sql);
			$query->bindValue('key', $key, PDO::PARAM_STR);
			$query->bindValue('id', $id, PDO::PARAM_STR);
			$query->bindValue('lti_context_id', $resource_link->lti_context_id, PDO::PARAM_STR);
			$query->bindValue('lti_resource_id', $resource_link->lti_resource_id, PDO::PARAM_STR);
			$query->bindValue('title', $resource_link->title, PDO::PARAM_STR);
			$query->bindValue('settings', $settingsValue, PDO::PARAM_STR);
			$query->bindValue('primary_consumer_key', $resource_link->primary_consumer_key, PDO::PARAM_STR);
			$query->bindValue('primary_context_id', $resource_link->primary_resource_link_id, PDO::PARAM_STR);
			$query->bindValue('share_approved', $resource_link->share_approved, PDO::PARAM_INT);
			$query->bindValue('created', $now, PDO::PARAM_STR);
			$query->bindValue('modified', $now, PDO::PARAM_STR);
		} else if ($id == $previous_id) {
			$sql = 'UPDATE ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' ' .
						 'SET lti_context_id = :lti_context_id, lti_resource_id = :lti_resource_id, title = :title, settings = :settings, ' .
						 'primary_consumer_key = :primary_consumer_key, primary_context_id = :primary_context_id, share_approved = :share_approved, modified = :modified ' .
						 'WHERE (consumer_key = :key) AND (context_id = :id)';
			$query = $this->db->prepare($sql);
			$query->bindValue('key', $key, PDO::PARAM_STR);
			$query->bindValue('id', $id, PDO::PARAM_STR);
			$query->bindValue('lti_context_id', $resource_link->lti_context_id, PDO::PARAM_STR);
			$query->bindValue('lti_resource_id', $resource_link->lti_resource_id, PDO::PARAM_STR);
			$query->bindValue('title', $resource_link->title, PDO::PARAM_STR);
			$query->bindValue('settings', $settingsValue, PDO::PARAM_STR);
			$query->bindValue('primary_consumer_key', $resource_link->primary_consumer_key, PDO::PARAM_STR);
			$query->bindValue('primary_context_id', $resource_link->primary_resource_link_id, PDO::PARAM_STR);
			$query->bindValue('share_approved', $resource_link->share_approved, PDO::PARAM_INT);
			$query->bindValue('modified', $now, PDO::PARAM_STR);
		} else {
			$sql = 'UPDATE ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' ' .
						 'SET context_id = :new_id, lti_context_id = :lti_context_id, lti_resource_id = :lti_resource_id, title = :title, settings = :settings, ' .
						 'primary_consumer_key = :primary_consumer_key, primary_context_id = :primary_context_id, share_approved = :share_approved, modified = :modified ' .
						 'WHERE (consumer_key = :key) AND (context_id = :old_id)';
			$query = $this->db->prepare($sql);
			$query->bindValue('key', $key, PDO::PARAM_STR);
			$query->bindValue('old_id', $previous_id, PDO::PARAM_STR);
			$query->bindValue('new_id', $id, PDO::PARAM_STR);
			$query->bindValue('lti_context_id', $resource_link->lti_context_id, PDO::PARAM_STR);
			$query->bindValue('lti_resource_id', $resource_link->lti_resource_id, PDO::PARAM_STR);
			$query->bindValue('title', $resource_link->title, PDO::PARAM_STR);
			$query->bindValue('settings', $settingsValue, PDO::PARAM_STR);
			$query->bindValue('primary_consumer_key', $resource_link->primary_consumer_key, PDO::PARAM_STR);
			$query->bindValue('primary_context_id', $resource_link->primary_resource_link_id, PDO::PARAM_STR);
			$query->bindValue('share_approved', $resource_link->share_approved, PDO::PARAM_INT);
			$query->bindValue('modified', $now, PDO::PARAM_STR);
		}
		$ok = $query->execute();
		if ($ok) {
			if (is_null($resource_link->created)) {
				$resource_link->created = $time;
			}
			$resource_link->modified = $time;
		}

		return $ok;

	}

###
#    Delete the resource link from the database
###
	public function Resource_Link_delete($resource_link) {

		$key = $resource_link->getKey();
		$id = $resource_link->getId();
// Delete any outstanding share keys for resource links for this consumer
		$sql = 'DELETE FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_SHARE_KEY_TABLE_NAME . ' ' .
					 'WHERE (primary_consumer_key = :key) AND (primary_context_id = :id)';
		$query = $this->db->prepare($sql);
		$query->bindValue('key', $key, PDO::PARAM_STR);
		$query->bindValue('id', $id, PDO::PARAM_STR);
		$ok = $query->execute();

// Delete users
		if ($ok) {
			$sql = 'DELETE FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::USER_TABLE_NAME . ' ' .
						 'WHERE (consumer_key = :key) AND (context_id = :id)';
			$query = $this->db->prepare($sql);
			$query->bindValue('key', $key, PDO::PARAM_STR);
			$query->bindValue('id', $id, PDO::PARAM_STR);
			$ok = $query->execute();
		}

// Update any resource links for which this is the primary resource link
		if ($ok) {
			$sql = 'UPDATE ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' ' .
						 'SET primary_consumer_key = NULL, primary_context_id = NULL ' .
						 'WHERE (primary_consumer_key = :key) AND (primary_context_id = :id)';
			$query = $this->db->prepare($sql);
			$query->bindValue('key', $key, PDO::PARAM_STR);
			$query->bindValue('id', $id, PDO::PARAM_STR);
			$ok = $query->execute();
		}

// Delete resource link
		if ($ok) {
			$sql = 'DELETE FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' ' .
						 'WHERE (consumer_key = :key) AND (context_id = :id)';
			$query = $this->db->prepare($sql);
			$query->bindValue('key', $key, PDO::PARAM_STR);
			$query->bindValue('id', $id, PDO::PARAM_STR);
			$ok = $query->execute();
		}

		if ($ok) {
			$resource_link->initialise();
		}

		return $ok;

	}

###
#    Obtain an array of LTI_User objects for users with a result sourcedId.  The array may include users from other
#    resource links which are sharing this resource link.  It may also be optionally indexed by the user ID of a specified scope.
###
	public function Resource_Link_getUserResultSourcedIDs($resource_link, $local_only, $id_scope) {

		$users = array();

		if ($local_only) {
			$sql = 'SELECT u.consumer_key, u.context_id, u.user_id, u.lti_result_sourcedid ' .
						 'FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::USER_TABLE_NAME . ' u ' .
						 'INNER JOIN ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' c ' .
						 'ON u.consumer_key = c.consumer_key AND u.context_id = c.context_id ' .
						 'WHERE (c.consumer_key = :key) AND (c.context_id = :id) AND (c.primary_consumer_key IS NULL) AND (c.primary_context_id IS NULL)';
		} else {
			$sql = 'SELECT u.consumer_key, u.context_id, u.user_id, u.lti_result_sourcedid ' .
						 'FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::USER_TABLE_NAME . ' u ' .
						 'INNER JOIN ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' c ' .
						 'ON u.consumer_key = c.consumer_key AND u.context_id = c.context_id ' .
						 'WHERE ((c.consumer_key = :key) AND (c.context_id = :id) AND (c.primary_consumer_key IS NULL) AND (c.primary_context_id IS NULL)) OR ' .
						 '((c.primary_consumer_key = :key) AND (c.primary_context_id = :id) AND (share_approved = 1))';
		}
		$key = $resource_link->getKey();
		$id = $resource_link->getId();
		$query = $this->db->prepare($sql);
		$query->bindValue('key', $key, PDO::PARAM_STR);
		$query->bindValue('id', $id, PDO::PARAM_STR);
		if ($query->execute()) {
			while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
				$row = array_change_key_case($row);
				$user = new LTI_User($resource_link, $row['user_id']);
				$user->consumer_key = $row['consumer_key'];
				$user->context_id = $row['context_id'];
				$user->lti_result_sourcedid = $row['lti_result_sourcedid'];
				if (is_null($id_scope)) {
					$users[] = $user;
				} else {
					$users[$user->getId($id_scope)] = $user;
				}
			}
		}

		return $users;

	}

###
#    Get an array of LTI_Resource_Link_Share objects for each resource link which is sharing this resource link
###
	public function Resource_Link_getShares($resource_link) {

		$shares = array();

		$key = $resource_link->getKey();
		$id = $resource_link->getId();
		$sql = 'SELECT consumer_key, context_id, title, share_approved ' .
					 'FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::RESOURCE_LINK_TABLE_NAME . ' ' .
					 'WHERE (primary_consumer_key = :key) AND (primary_context_id = :id) ' .
					 'ORDER BY consumer_key';
		$query = $this->db->prepare($sql);
		$query->bindValue('key', $key, PDO::PARAM_STR);
		$query->bindValue('id', $id, PDO::PARAM_STR);
		if ($query->execute()) {
			while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
				$row = array_change_key_case($row);
				$share = new LTI_Resource_Link_Share();
				$share->consumer_key = $row['consumer_key'];
				$share->resource_link_id = $row['context_id'];
				$share->title = $row['title'];
				$share->approved = ($row['share_approved'] == 1);
				$shares[] = $share;
			}
		}

		return $shares;

	}
}
