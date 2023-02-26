<script>
  import { createEventDispatcher, getContext, onMount } from 'svelte';
  import { slide } from 'svelte/transition';
  import FilterGroup from './FilterGroup.svelte';
  import FilterApplied from './FilterApplied.svelte';
  import { normalizeOptions, shallowCompare } from './util';
  import ProjectIcon from './Project/ProjectIcon.svelte';
  import {
    filters,
    rowsCount,
    filtersVocabularies,
    moduleCategoryFilter,
    moduleCategoryVocabularies,
    sort,
    searchString,
    sortCriteria,
  } from './stores';
  import {
    COVERED_ID,
    ACTIVELY_MAINTAINED_ID,
    MAINTENANCE_OPTIONS,
    DEVELOPMENT_OPTIONS,
    SECURITY_OPTIONS,
    ALL_VALUES_ID,
    FULL_MODULE_PATH,
    DARK_COLOR_SCHEME,
  } from './constants';

  const { Drupal } = window;
  const { announce } = Drupal;
  const dispatch = createEventDispatcher();
  const stateContext = getContext('state');

  export const filter = (row, text) =>
    Object.values(row).filter(
      (item) =>
        item && item.toString().toLowerCase().indexOf(text.toLowerCase()) > 1,
    ).length > 0;
  export let index = -1;
  export let searchText;
  searchString.subscribe((value) => {
    searchText = value;
  });
  export let labels = {
    placeholder: Drupal.t('Module Name, Keyword(s), etc.'),
  };

  let isOpen = false;
  let sortMatch = $sortCriteria.find((option) => option.id === $sort);
  if (typeof sortMatch === 'undefined') {
    $sort = $sortCriteria[0].id;
    sortMatch = $sortCriteria.find((option) => option.id === $sort);
  }
  let sortText = sortMatch.text;

  /**
   * Refreshes the live region after a filter or search completes.
   */
  const refreshLiveRegion = () => {
    if ($rowsCount) {
      // Set announce() to an empty string. This ensures the result count will
      // be announced after filtering even if the count is the same.
      announce('');

      // The announcement is delayed by 210 milliseconds, a wait that is
      // slightly longer than the 200 millisecond debounce() built into
      // announce(). This ensures that the above call to reset the aria live
      // region to an empty string actually takes place instead of being
      // debounced.
      setTimeout(() => {
        announce(
          Drupal.t('@count Results, Sorted by @sortText', {
            '@count': $rowsCount
              .toString()
              .replace(/\B(?=(\d{3})+(?!\d))/g, ','),
            '@sortText': sortText,
          }),
        );
      }, 210);
    }
  };

  const updateVocabularies = (vocabulary, value) => {
    const normalizedValue = normalizeOptions(value);
    const storedValue = JSON.parse(localStorage.getItem(`pb.${vocabulary}`));
    if (storedValue === null || !shallowCompare(normalizedValue, storedValue)) {
      $filtersVocabularies[vocabulary] = normalizedValue;
      localStorage.setItem(`pb.${vocabulary}`, JSON.stringify(normalizedValue));
    }
  };

  onMount(() => {
    updateVocabularies('developmentStatus', DEVELOPMENT_OPTIONS);
    updateVocabularies('maintenanceStatus', MAINTENANCE_OPTIONS);
    updateVocabularies('securityCoverage', SECURITY_OPTIONS);
  });

  async function onSearch(event) {
    const state = stateContext.getState();
    const detail = {
      originalEvent: event,
      filter,
      index,
      searchText,
      page: state.page,
      pageIndex: state.pageIndex,
      pageSize: state.pageSize,
      rows: state.filteredRows,
    };
    dispatch('search', detail);

    if (detail.preventDefault !== true) {
      if (detail.searchText.length === 0) {
        stateContext.setRows(state.rows);
      } else {
        stateContext.setRows(
          detail.rows.filter((r) => detail.filter(r, detail.searchText, index)),
        );
      }
      stateContext.setPage(0, 0);
    } else {
      stateContext.setRows(detail.rows);
    }
    refreshLiveRegion();
  }

  async function onSort(event) {
    const state = stateContext.getState();
    const detail = {
      originalEvent: event,
      page: state.page,
      pageIndex: state.pageIndex,
      pageSize: state.pageSize,
      rows: state.filteredRows,
      sort: $sort,
    };
    dispatch('sort', detail);
    stateContext.setPage(0, 0);
    stateContext.setRows(detail.rows);
    sortText = $sortCriteria.find((option) => option.id === $sort).text;
    refreshLiveRegion();
  }

  async function onAdvancedFilter(event) {
    const state = stateContext.getState();
    const detail = {
      originalEvent: event,
      developmentStatus: $filters.developmentStatus,
      maintenanceStatus: $filters.maintenanceStatus,
      securityCoverage: $filters.securityCoverage,
      page: state.page,
      pageIndex: state.pageIndex,
      pageSize: state.pageSize,
      rows: state.filteredRows,
    };
    dispatch('advancedFilter', detail);
    stateContext.setPage(0, 0);
    stateContext.setRows(detail.rows);
    refreshLiveRegion();
  }

  /* When the user clicks on the button,
  toggle between hiding and showing the dropdown content */
  function openDropdown() {
    isOpen = !isOpen;
  }

  function onSelectCategory(event) {
    const state = stateContext.getState();
    const detail = {
      originalEvent: event,
      category: $moduleCategoryFilter,
      page: state.page,
      pageIndex: state.pageIndex,
      pageSize: state.pageSize,
      rows: state.filteredRows,
    };
    dispatch('selectCategory', detail);
    stateContext.setPage(0, 0);
    stateContext.setRows(detail.rows);
  }

  function removeFilter(filterType) {
    $filters[filterType] = ALL_VALUES_ID;
    $filters = $filters;
    onAdvancedFilter();
  }
</script>

<form class="search__form">
  <div
    class="search__form-item js-form-item form-item js-form-type-textfield form-type--textfield"
    role="search"
  >
    <label for="pb-text" class="form-item__label"
      >{Drupal.t('Search for modules')}</label
    >
    <div class="search__search-bar">
      <input
        class="search__searchterm form-text form-element form-element--type-text"
        type="search"
        title={labels.placeholder}
        placeholder={labels.placeholder}
        id="pb-text"
        name="text"
        bind:value={$searchString}
        on:keyup={Drupal.debounce(onSearch, 250, false)}
      />
      <img
        class="search__search-icon"
        id="search-icon"
        src="{FULL_MODULE_PATH}/images/search-icon{DARK_COLOR_SCHEME
          ? '--dark-color-scheme'
          : ''}.svg"
        alt=""
      />
    </div>
  </div>
  <div
    class="search__grid-container js-form-item js-form-type-select form-type--select js-form-item-type form-item--type"
  >
    <section aria-label={Drupal.t('Search results')}>
      <div class="search__results-count">
        <span id="output">
          {$rowsCount &&
            $rowsCount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')}
          {Drupal.t('Results')}
          <span class="visually-hidden"
            >{Drupal.t('Sorted by @sortText', { '@sortText': sortText })}</span
          >
        </span>

        {#if $filters.developmentStatus}
          <FilterApplied
            id={$filters.developmentStatus}
            label={$filtersVocabularies.developmentStatus[
              $filters.developmentStatus
            ]}
            clickHandler={() => removeFilter('developmentStatus')}
          />
        {/if}

        {#if $filters.maintenanceStatus}
          <FilterApplied
            id={$filters.maintenanceStatus}
            label={$filtersVocabularies.maintenanceStatus[
              $filters.maintenanceStatus
            ]}
            clickHandler={() => removeFilter('maintenanceStatus')}
          />
        {/if}

        {#if $filters.securityCoverage}
          <FilterApplied
            id={$filters.securityCoverage}
            label={$filtersVocabularies.securityCoverage[
              $filters.securityCoverage
            ]}
            clickHandler={() => removeFilter('securityCoverage')}
          />
        {/if}

        {#each $moduleCategoryFilter as category}
          <FilterApplied
            id={category}
            label={$moduleCategoryVocabularies[category]}
            clickHandler={() => {
              $moduleCategoryFilter.splice(
                $moduleCategoryFilter.indexOf(category),
                1,
              );
              $moduleCategoryFilter = $moduleCategoryFilter;
              onSelectCategory();
            }}
          />
        {/each}

        {#if $filters.securityCoverage !== ALL_VALUES_ID || $filters.maintenanceStatus !== ALL_VALUES_ID || $filters.developmentStatus !== ALL_VALUES_ID || $moduleCategoryFilter.length}
          <button
            class="search__filter-button"
            on:click={() => {
              $filters.maintenanceStatus = ALL_VALUES_ID;
              $filters.developmentStatus = ALL_VALUES_ID;
              $filters.securityCoverage = ALL_VALUES_ID;
              $filters = $filters;
              $moduleCategoryFilter = [];
              onAdvancedFilter();
              onSelectCategory();
            }}
          >
            {Drupal.t('Clear filters')}
          </button>
        {/if}
        {#if !($filters.maintenanceStatus === ACTIVELY_MAINTAINED_ID && $filters.securityCoverage === COVERED_ID && $filters.developmentStatus === ALL_VALUES_ID && $moduleCategoryFilter.length === 0)}
          <button
            class="search__filter-button"
            on:click={() => {
              $filters.maintenanceStatus = ACTIVELY_MAINTAINED_ID;
              $filters.securityCoverage = COVERED_ID;
              $filters.developmentStatus = ALL_VALUES_ID;
              $moduleCategoryFilter = [];
              $filters = $filters;
              onAdvancedFilter();
              onSelectCategory();
            }}
          >
            {Drupal.t('Recommended filters')}
          </button>
        {/if}
      </div>
    </section>
    <div class="search__sort">
      <label for="pb-sort">{Drupal.t('Sort by:')}</label>
      <select
        name="pb-sort"
        id="pb-sort"
        bind:value={$sort}
        on:change={onSort}
        class="search__sort-select form-select form-element form-element--type-select"
      >
        {#each $sortCriteria as opt}
          <option value={opt.id}>
            {opt.text}
          </option>
        {/each}
      </select>
    </div>
    <div class="search__filter">
      <section aria-label={Drupal.t('Filter settings')}>
        <button
          type="button"
          class="search__filter__toggle form-element"
          class:is_open={isOpen}
          aria-controls="filter-dropdown"
          aria-label={isOpen
            ? Drupal.t('Close Filter')
            : Drupal.t('Open Filter')}
          aria-expanded={isOpen.toString()}
          on:click={() => openDropdown()}
          ><img
            src="{FULL_MODULE_PATH}/images/advanced-filter-icon.svg"
            alt="advanced filter icon"
          />Filters
        </button>
      </section>
    </div>
  </div>
  <div class="search__dropdown dropdown-filters" id="filter-dropdown">
    {#if isOpen}
      <div class="search__filters" transition:slide>
        <FilterGroup
          filterTitle={Drupal.t('Development Status')}
          filterData={DEVELOPMENT_OPTIONS}
          filterType="developmentStatus"
          changeHandler={onAdvancedFilter}
          let:id
          let:label
        >
          <label
            slot="label"
            class="search__checkbox-label"
            for={`developmentStatus${id}`}
          >
            {label}
          </label>
        </FilterGroup>
        <FilterGroup
          filterTitle={Drupal.t('Maintenance Status')}
          filterData={MAINTENANCE_OPTIONS}
          filterType="maintenanceStatus"
          changeHandler={onAdvancedFilter}
          let:id
          let:label
        >
          <label
            slot="label"
            class="search__checkbox-label"
            for={`maintenanceStatus${id}`}
          >
            {label}
          </label>
        </FilterGroup>
        <FilterGroup
          filterTitle={Drupal.t('Security Advisory Coverage')}
          filterData={SECURITY_OPTIONS}
          filterType="securityCoverage"
          changeHandler={onAdvancedFilter}
          let:id
          let:label
        >
          <label
            slot="label"
            class="search__checkbox-label"
            for={`securityCoverage${id}`}
          >
            {label}
            {#if id === COVERED_ID}
              <span class="small-icons">
                <ProjectIcon type="status" />
              </span>
            {/if}
          </label>
        </FilterGroup>
      </div>
    {/if}
  </div>
</form>

<style>
  .search__form-item {
    margin-top: 0;
  }
  .search__form {
    margin-top: 2.375rem;
    display: inherit;
    flex-wrap: wrap;
    padding: 0 0 1.5rem;
    border: transparent;
    border-radius: 2px;
    background-color: #fff;
  }

  .search__search-bar .search__searchterm {
    width: 100%;
    outline: none;
    height: 50px;
    position: relative;
    display: flex;
  }

  .search__search-bar {
    height: 50px;
    text-align: center;
    color: #fff;
    cursor: pointer;
    font-size: 20px;
    position: relative;
    border: 1px solid #919297;
    border-radius: 2px;
  }

  .search__search-icon {
    position: absolute;
    bottom: 12px;
    inset-inline-end: 30px;
  }

  ::placeholder {
    font-family: sans-serif;
    font-style: normal;
    font-weight: 400;
    font-size: 16px;
    line-height: 150%;
    display: flex;
    align-items: center;
  }

  .search__filter__toggle > img {
    width: 20px;
    height: 14px;
    margin-bottom: -2px;
    margin-inline-end: 4px;
  }

  .search__filter__toggle.is_open {
    background-color: #adaeb3;
  }

  .dropdown-filters {
    position: relative;
    border: 3px solid #f3f4f9;
    z-index: 1;
  }

  .small-icons {
    margin-top: -3px;
    margin-inline-end: 1px;
  }

  .search__checkbox-label {
    font-weight: normal;
    padding-inline-start: 35px;
    display: flex;
  }

  .form-select:hover,
  .search__filter__toggle:hover {
    cursor: pointer;
  }

  #output {
    display: inline-block;
    font-family: sans-serif;
    font-style: normal;
    font-weight: 700;
    font-size: 14px;
    line-height: 21px;
    margin-inline-start: 20px;
  }

  .search__grid-container {
    display: grid;
    height: auto;
    grid-template-columns: 5fr auto auto;
    grid-gap: 20px;
    background: #f3f4f9;
    padding: 5px;
    align-items: center;
    max-width: 100%;
  }

  .search__sort-select {
    border: none;
    background-color: #d3d4d9;
  }

  .search__sort {
    z-index: 2;
  }

  .search__filter__toggle {
    background-color: #d3d4d9;
    color: black;
    padding-left: 16px;
    padding-right: 16px;
    font-size: 16px;
    border-radius: 2px;
    width: fit-content;
    margin-inline-start: 15px;
    text-decoration: underline;
  }

  .search__filter__toggle > img {
    padding: 0 0.25rem;
  }

  .search__filter-button {
    padding: 0 0.25rem;
    background: none;
    border: none;
    color: #013cc5;
    text-decoration: underline;
    cursor: pointer;
  }

  .search__filter {
    display: flex;
    margin-inline-end: 1em;
  }

  @media only screen and (min-width: 1200px) {
    .search__checkbox-label {
      font-weight: normal;
      padding-inline-start: 35px;
      display: flex;
      margin-right: unset;
    }
  }

  @media screen and (max-width: 855px) {
    .search__grid-container {
      display: block;
    }
    .search__sort {
      margin-bottom: 10px;
      margin-top: 10px;
    }
  }

  @media (forced-colors: active) {
    .search__filter__toggle {
      border: 1px solid;
    }
    #pb-sort {
      border: 1px solid;
    }
    @media (prefers-color-scheme: dark) {
      .search__filter__toggle > img {
        filter: invert(1);
      }
    }
  }
</style>
