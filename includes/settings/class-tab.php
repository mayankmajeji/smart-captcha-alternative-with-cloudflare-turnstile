<?php

/**
 * Base Settings Tab Class
 *
 * @package TurnstileWP
 */

declare(strict_types=1);

namespace TurnstileWP\Settings;

abstract class Tab {

	/**
	 * Tab ID
	 *
	 * @var string
	 */
	protected string $id;

	/**
	 * Tab Label
	 *
	 * @var string
	 */
	protected string $label;

	/**
	 * Tab Icon
	 *
	 * @var string
	 */
	protected string $icon;

	/**
	 * Tab Priority
	 *
	 * @var int
	 */
	protected int $priority;

	/**
	 * Tab Sections
	 *
	 * @var array
	 */
	protected array $sections;

	/**
	 * Get tab ID
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * Get tab label
	 */
	public function get_label(): string {
		return $this->label;
	}

	/**
	 * Get tab icon
	 */
	public function get_icon(): string {
		return $this->icon;
	}

	/**
	 * Get tab priority
	 */
	public function get_priority(): int {
		return $this->priority;
	}

	/**
	 * Get tab sections
	 */
	public function get_sections(): array {
		return $this->sections;
	}

	/**
	 * Get tab content
	 */
	abstract public function get_content(): string;
}
