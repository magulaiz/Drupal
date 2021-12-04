<?php

namespace Drupal\update;

class UpdateServerProjectInfo {


  /**
   * @var array
   */
  protected $data;

  private function __construct(array $data) {
    $this->data = $data;
  }

  public static function createFromArray(array $available): UpdateServerProjectInfo {
    return new static($available);
  }



  public function getProjectStatus(): ?string {
    return $available['project_status'] ?? NULL;
  }

  public function getSupportBranches(): array {
    if (isset($this->data['supported_branches'])) {
      return explode(',', $this->data['supported_branches']);
    }
    return [];
  }

  public function getReleases() {
    return $this->data['releases'] ?? [];
  }

}
