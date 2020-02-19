<?php
/**
 *
 * MIT License
 *
 * Copyright (C) 2020  Kirill Yegorov https://github.com/k-samuel
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */
declare(strict_types=1);

namespace KSamuel\FacetedSearch;

use KSamuel\FacetedSearch\Filter\FilterInterface;

class Search
{
    /**
     * @var Index
     */
    protected $index;

    /**
     * Search constructor.
     * @param Index $index
     */
    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    /**
     * Find records by filters
     * @param array<FilterInterface> $filters
     * @param array<int>|null $inputRecords - list of record id to search in. Use it for limit results
     * @return array<int>
     */
    public function find(array $filters, ?array $inputRecords = null) : array
    {
        $result = $inputRecords;
        /**
         * @var FilterInterface $filter
         */
        foreach ($filters as $filter){
            $indexData = $this->index->getFieldData($filter->getFieldName());
            if(empty($indexData)){
                return [];
            }
            $result = $filter->filterResults($indexData, $result);
        }
        if(empty($result)){
            $result = [];
        }
        return $result;
    }

    /**
     * @param array $filters
     * @return array
     */
    public function findAcceptableFilters(array $filters = []): array
    {
        $result = [];
        $facetsData = $this->index->getData();

        foreach ($facetsData as $filterName => $filterValues) {
            if(empty($filters)){
                $result[$filterName] = array_keys($filterValues);
            }else{
                $filtersCopy = $filters;
                unset($filtersCopy[$filterName]);
                $recordIds = $this->find($filtersCopy);
                foreach ($filterValues as $filterValue => $data) {
                    if (!empty(array_intersect($data, $recordIds))) {
                        $result[$filterName][] = $filterValue;
                    }
                }
            }
        }
        return $result;
    }
}