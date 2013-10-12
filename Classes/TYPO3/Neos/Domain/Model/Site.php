<?php
namespace TYPO3\Neos\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * Domain model of a site
 *
 * @Flow\Scope("prototype")
 * @Flow\Entity
 * @api
 */
class Site {

	/**
	 * Site states
	 */
	const STATE_ONLINE = 1;
	const STATE_OFFLINE = 2;

	/**
	 * Name of the site
	 *
	 * @var string
	 * @Flow\Validate(type="Label")
	 * @Flow\Validate(type="NotEmpty")
	 * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=250 })
	 */
	protected $name = 'Untitled Site';

	/**
	 * Node name of this site in the content repository.
	 *
	 * The first level of nodes of a site can be reached via a path like
	 * "/Sites/MySite/" where "MySite" is the nodeName.
	 *
	 * @var string
	 * @Flow\Identity
	 * @Flow\Validate(type="NotEmpty")
	 * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=250 })
	 * @Flow\Validate(type="\TYPO3\Neos\Validation\Validator\NodeNameValidator")
	 */
	protected $nodeName;

	/**
	 * @var \Doctrine\Common\Collections\Collection<\TYPO3\Neos\Domain\Model\Domain>
	 * @ORM\OneToMany(mappedBy="site")
	 * @Flow\Lazy
	 */
	protected $domains;

	/**
	 * The site's state
	 *
	 * @var integer
	 * @Flow\Validate(type="NumberRange", options={ "minimum"=1, "maximum"=2 })
	 */
	protected $state = self::STATE_OFFLINE;

	/**
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 */
	protected $siteResourcesPackageKey;

	/**
	 * Constructs this Site object
	 *
	 * @param string $nodeName Node name of this site in the content repository
	 */
	public function __construct($nodeName) {
		$this->nodeName = $nodeName;
		$this->domains = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * @return string
	 */
	function __toString() {
		return $this->getNodeName();
	}

	/**
	 * Sets the name for this site
	 *
	 * @param string $name The site name
	 * @return void
	 * @api
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Returns the name of this site
	 *
	 * @return string The name
	 * @api
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns the node name of this site
	 *
	 * If you need to fetch the root node for this site, use the content
	 * context, do not use the NodeDataRepository!
	 *
	 * @return string The node name
	 */
	public function getNodeName() {
		return $this->nodeName;
	}

	/**
	 * Sets the node name for this site
	 *
	 * @param string $nodeName The site node name
	 * @return void
	 * @api
	 */
	public function setNodeName($nodeName) {
		$this->nodeName = $nodeName;
	}

	/**
	 * Sets the state for this site
	 *
	 * @param integer $state The site's state, must be one of the STATUS_* constants
	 * @return void
	 * @api
	 */
	public function setState($state) {
		$this->state = $state;
	}

	/**
	 * Returns the state of this site
	 *
	 * @return integer The state - one of the STATUS_* constant's values
	 * @api
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * Sets the key of a package containing the static resources for this site.
	 *
	 * @param string $packageKey The package key
	 * @return void
	 * @api
	 */
	public function setSiteResourcesPackageKey($packageKey) {
		$this->siteResourcesPackageKey = $packageKey;
	}

	/**
	 * Returns the key of a package containing the static resources for this site.
	 *
	 * @return string The package key
	 * @api
	 */
	public function getSiteResourcesPackageKey() {
		return $this->siteResourcesPackageKey;
	}

	/**
	 * @return boolean
	 * @api
	 */
	public function isOnline() {
		return $this->state === self::STATE_ONLINE;
	}

	/**
	 * @return boolean
	 * @api
	 */
	public function isOffline() {
		return $this->state === self::STATE_OFFLINE;
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection<\TYPO3\Neos\Domain\Model\Domain> $domains
	 */
	public function setDomains($domains) {
		$this->domains = $domains;
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection<\TYPO3\Neos\Domain\Model\Domain>
	 */
	public function getDomains() {
		return $this->domains;
	}

	/**
	 * @return boolean TRUE if the site has at least one active domain assigned
	 */
	public function hasActiveDomains() {
		return $this->domains->exists(function($index, $domain) { return $domain->getActive(); });
	}

	/**
	 * @return \TYPO3\Neos\Domain\Model\Domain
	 */
	public function getFirstActiveDomain() {
		$activeDomains = $this->domains->filter(function($domain) { return $domain->getActive(); });
		return $activeDomains->first();
	}

	/**
	 * Internal event handler to forward site changes to the "siteChanged" signal
	 *
	 * @ORM\PostPersist
	 * @ORM\PostUpdate
	 * @ORM\PostRemove
	 * @return void
	 */
	public function onPostFlush() {
		$this->emitSiteChanged();
	}

	/**
	 * Internal signal
	 *
	 * @Flow\Signal
	 * @return void
	 */
	public function emitSiteChanged() {}

}
