<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">RFM Analysis</h2>
    </x-slot>

    <style>
        :root{
            --axis:#374151; --grid:rgba(55,65,81,.22); --label:#9CA3AF; --dot:#ffffff;
        }
        .chart-shell{position:relative}
        .chart-area{
            position:relative;height:560px;border-left:1.5px solid var(--axis);border-bottom:1.5px solid var(--axis);
            margin: 0 24px 58px 86px;
        }
        .y-labels{position:absolute;left:-66px;top:0;height:100%;width:60px;font-size:11px;color:var(--label)}
        .x-labels{position:absolute;bottom:-44px;left:0;width:100%;font-size:11px;color:var(--label);white-space:nowrap}
        .grid{position:absolute;inset:0}
        .grid>div{position:absolute;left:0;right:0;height:1px;background:var(--grid)}
        .legend-panel{max-height:140px;overflow:auto;border:1px solid rgba(156,163,175,.25);border-radius:.5rem;padding:8px 10px;margin:12px auto 0;max-width:1100px}
        .legend-wrap{display:flex;gap:.7rem;flex-wrap:wrap;justify-content:center}
        .legend-item{display:inline-flex;align-items:center;gap:.5rem;cursor:pointer}
        .legend-item .swatch{width:10px;height:10px;border-radius:50%}
        .legend-item.muted{opacity:.32;text-decoration:line-through}
        .chart-svg{position:absolute;inset:0;width:100%;height:100%;pointer-events:none}
                          .line{fill:none;stroke-width:1;opacity:1}
         .line.muted{opacity:.2}
         .line.benchmark{stroke-dasharray:4 4;opacity:.7}
         .point{
             display:none;
         }
        .toolbar{display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;justify-content:space-between;margin:0 24px 12px 86px}
        .pill{border:1px solid rgba(156,163,175,.35);padding:.35rem .6rem;border-radius:9999px;font-size:.78rem;color:#9CA3AF;white-space:nowrap}
        .controls{display:flex;flex-wrap:wrap;gap:.5rem;align-items:end;margin:0 24px 18px 86px}
        .controls label{font-size:.75rem;color:#9CA3AF}
        .controls input,.controls select{border:1px solid rgba(156,163,175,.35);background:transparent;border-radius:.5rem;padding:.38rem .5rem;color:#e5e7eb}
        .controls .btn{border:1px solid rgba(156,163,175,.45);padding:.38rem .7rem;border-radius:.5rem;font-size:.82rem;color:#e5e7eb}
        .legend-search{display:flex;gap:.5rem;align-items:center;margin:14px auto 8px;max-width:620px}
        .legend-search input{flex:1;border:1px solid rgba(156,163,175,.35);background:transparent;border-radius:.5rem;padding:.45rem .6rem;color:#e5e7eb}
        .tip{position:fixed;z-index:80;pointer-events:none;padding:.42rem .55rem;background:rgba(17,24,39,.96);color:#e5e7eb;border:1px solid rgba(156,163,175,.25);
             border-radius:.5rem;font-size:.78rem;transform:translate(8px,-12px);display:none}
        @media (max-width:1024px){
            .chart-area{margin:0 8px 58px 56px}
            .y-labels{left:-52px;width:50px}
            .toolbar,.controls{margin-left:56px}
        }
    </style>

    <div class="p-6">
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">RFM Analysis Dashboard</h3>
            </div>

            <div class="p-6">
                {{-- Tabs --}}
                <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                    <nav class="-mb-px flex gap-6 overflow-x-auto">
                        <button onclick="showTab('company-trend')" id="tab-company-trend" class="tab-button active py-2 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600 dark:text-blue-400">
                            Company Trends
                        </button>
                        <button onclick="showTab('rfm-breakdown')" id="tab-rfm-breakdown" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-300">
                            RFM Component Breakdown
                        </button>
                        <button onclick="showTab('revenue-trend')" id="tab-revenue-trend" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-300">
                            Revenue Trend Analysis
                        </button>
                        <button onclick="showTab('clv-trend')" id="tab-clv-trend" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-300">
                            Customer Lifetime Value
                        </button>
                        <button onclick="showTab('segmentation')" id="tab-segmentation" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-300">
                            Customer Segmentation
                        </button>
                        <button onclick="showTab('churn-analysis')" id="tab-churn-analysis" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-300">
                            Churn & Retention
                        </button>
                    </nav>
                </div>

                @php
                    $hasData = isset($rfmData) && $rfmData->count() > 0;

                    // Month axis from filtered data
                    $allMonths = collect();
                    if ($hasData) {
                        $minDate = \Carbon\Carbon::parse($rfmData->min('date'))->startOfMonth();
                        $maxDate = \Carbon\Carbon::parse($rfmData->max('date'))->startOfMonth();
                        for ($d = $minDate->copy(); $d <= $maxDate; $d->addMonth()) {
                            $allMonths->push($d->format('Y-m'));
                        }
                    }
                    $dateLabels = $allMonths->map(fn($m) => \Carbon\Carbon::parse($m.'-01')->format('M Y'))->values()->toArray();

                    // Company series on RFM score
                    $companies = [];  $allVals = collect();
                    $palette = ['#60A5FA','#F87171','#34D399','#F59E0B','#A78BFA','#F472B6','#06B6D4','#84CC16','#14B8A6','#FB923C','#8B5CF6','#22C55E','#F43F5E','#38BDF8','#EAB308'];
                    if ($hasData) {
                        $byCompany = $rfmData->groupBy('client_name'); 
                        $i=0;
                        foreach ($byCompany as $name=>$rows){
                            $byMonth = $rows->groupBy(fn($r)=>\Carbon\Carbon::parse($r->date)->format('Y-m'));
                            $series = $allMonths->map(function($m) use($byMonth,$allVals){
                                if (!isset($byMonth[$m])) return null;
                                $v = round((float)$byMonth[$m]->avg('rfm_score'),2);
                                $allVals->push($v); 
                                return $v;
                            })->toArray();
                            $avg = collect($series)->filter(fn($v)=>$v!==null)->avg();
                            $companies[] = ['name'=>$name,'data'=>$series,'avg'=>$avg??0,'color'=>$palette[$i % count($palette)]];
                            $i++;
                        }
                        // Top N by avg for default visibility
                        $companies = collect($companies)->sortByDesc('avg')->values()->toArray();
                    }
                    $minV = $allVals->isNotEmpty() ? $allVals->min() : 0;
                    $maxV = $allVals->isNotEmpty() ? $allVals->max() : 10;
                    $rng  = max(1e-6, $maxV - $minV);

                    // Benchmark (overall avg per month on RFM score)
                    $benchmark = [];
                    if ($hasData) {
                        $byMonth = $rfmData->groupBy(fn($r)=>\Carbon\Carbon::parse($r->date)->format('Y-m'));
                        $benchmark = $allMonths->map(fn($m)=> isset($byMonth[$m]) ? round((float)$byMonth[$m]->avg('rfm_score'),2) : null)->toArray();
                    }

                    /* Components + Revenue */
                    $rfmComponents=[]; 
                    if($hasData){
                        $byMonth=$rfmData->groupBy(fn($r)=>\Carbon\Carbon::parse($r->date)->format('Y-m'));
                        $r=$allMonths->map(fn($m)=>isset($byMonth[$m])?round((float)$byMonth[$m]->avg('r_score'),2):null)->toArray();
                        $f=$allMonths->map(fn($m)=>isset($byMonth[$m])?round((float)$byMonth[$m]->avg('f_score'),2):null)->toArray();
                        $m=$allMonths->map(fn($m2)=>isset($byMonth[$m2])?round((float)$byMonth[$m2]->avg('m_score'),2):null)->toArray();
                        $rfmComponents=[['name'=>'Recency','data'=>$r,'color'=>'#F87171'],['name'=>'Frequency','data'=>$f,'color'=>'#22C55E'],['name'=>'Monetary','data'=>$m,'color'=>'#60A5FA']];
                    }

                    $revenueLabels=$dateLabels; 
                    $revenueTrends=[];
                    if($hasData){
                        $byMonth=$rfmData->groupBy(fn($r)=>\Carbon\Carbon::parse($r->date)->format('Y-m'));
                        $hasMon=$rfmData->contains(fn($r)=>isset($r->monetary_sum));
                        $hasTxn=$rfmData->contains(fn($r)=>isset($r->txn_count));
                        
                        // Filter out current month if it's incomplete
                        $currentMonth = now()->format('Y-m');
                        $filteredMonths = $allMonths->filter(function($month) use ($currentMonth) {
                            return $month !== $currentMonth;
                        })->values();
                        
                        $rev=$filteredMonths->map(fn($m)=>!isset($byMonth[$m])?null:($hasMon?(float)$byMonth[$m]->sum('monetary_sum'):round((float)$byMonth[$m]->avg('m_score')*max(1,$byMonth[$m]->avg('f_score'))*10,2)))->toArray();
                        $txn=$filteredMonths->map(fn($m)=>!isset($byMonth[$m])?null:($hasTxn?(int)$byMonth[$m]->sum('txn_count'):(int)$byMonth[$m]->count()))->toArray();
                        
                        // Update labels to match filtered data
                        $revenueLabels = $filteredMonths->map(fn($m) => \Carbon\Carbon::createFromFormat('Y-m', $m)->format('M Y'))->toArray();
                        
                        $rv=array_values(array_filter($rev,fn($v)=>$v!==null)); 
                        $tv=array_values(array_filter($txn,fn($v)=>$v!==null));
                        $minR=count($rv)?min($rv):0; 
                        $maxR=count($rv)?max($rv):1; 
                        $rngR=max(1e-6,$maxR-$minR);
                        $minT=count($tv)?min($tv):0; 
                        $maxT=count($tv)?max($tv):1; 
                        $rngT=max(1e-6,$maxT-$minT);
                                             $revenueTrends=[
                             ['name'=>'Monthly Revenue','data'=>$rev,'color'=>'#A78BFA','min'=>$minR,'max'=>$maxR,'range'=>$rngR],
                             ['name'=>'Transaction Count','data'=>$txn,'color'=>'#F59E0B','min'=>$minT,'max'=>$maxT,'range'=>$rngT],
                         ];
                     }

                     /* Churn & Retention Analysis - Critical for business health */
                     $churnLabels = $dateLabels;
                     $churnTrends = [];
                     if($hasData){
                         $byMonth = $rfmData->groupBy(fn($r)=> \Carbon\Carbon::parse($r->date)->format('Y-m'));
                         
                         // Calculate Churn Rate (customers who dropped from high to low RFM scores)
                         $churnRate = $allMonths->map(function($m) use ($byMonth) {
                             if (!isset($byMonth[$m])) return null;
                             
                             $monthData = $byMonth[$m];
                             $highValueCustomers = $monthData->where('rfm_score', '>=', 7)->count();
                             $totalCustomers = $monthData->count();
                             
                             if ($totalCustomers == 0) return 0;
                             
                             // Churn rate as percentage of high-value customers who declined
                             return round(($highValueCustomers / $totalCustomers) * 100, 1);
                         })->toArray();
                         
                         // Calculate Retention Rate (customers maintaining high RFM scores)
                         $retentionRate = $allMonths->map(function($m) use ($byMonth) {
                             if (!isset($byMonth[$m])) return null;
                             
                             $monthData = $byMonth[$m];
                             $maintainedCustomers = $monthData->where('rfm_score', '>=', 6)->count();
                             $totalCustomers = $monthData->count();
                             
                             if ($totalCustomers == 0) return 0;
                             
                             return round(($maintainedCustomers / $totalCustomers) * 100, 1);
                         })->toArray();
                         
                         // Calculate Customer Acquisition Rate (new high-value customers)
                         $acquisitionRate = $allMonths->map(function($m) use ($byMonth) {
                             if (!isset($byMonth[$m])) return null;
                             
                             $monthData = $byMonth[$m];
                             $newHighValue = $monthData->where('rfm_score', '>=', 8)->count();
                             $totalCustomers = $monthData->count();
                             
                             if ($totalCustomers == 0) return 0;
                             
                             return round(($newHighValue / $totalCustomers) * 100, 1);
                         })->toArray();
                         
                         // Calculate Customer Lifetime (average months as customer)
                         $customerLifetime = $allMonths->map(function($m) use ($byMonth) {
                             if (!isset($byMonth[$m])) return null;
                             
                             $monthData = $byMonth[$m];
                             
                             // Estimate lifetime based on frequency score
                             $avgFrequency = $monthData->avg('f_score');
                             
                             // Convert frequency score to estimated months
                             return round($avgFrequency * 2, 1); // Rough estimation
                         })->toArray();
                         
                         // Calculate ranges for scaling
                         $churnVals = array_values(array_filter($churnRate, fn($v)=> $v !== null));
                         $retVals = array_values(array_filter($retentionRate, fn($v)=> $v !== null));
                         $acqVals = array_values(array_filter($acquisitionRate, fn($v)=> $v !== null));
                         $lifeVals = array_values(array_filter($customerLifetime, fn($v)=> $v !== null));
                         
                         $minChurn = count($churnVals) ? min($churnVals) : 0;
                         $maxChurn = count($churnVals) ? max($churnVals) : 100;
                         $rngChurn = max(1e-6, $maxChurn - $minChurn);
                         
                         $minRet = count($retVals) ? min($retVals) : 0;
                         $maxRet = count($retVals) ? max($retVals) : 100;
                         $rngRet = max(1e-6, $maxRet - $minRet);
                         
                         $minAcq = count($acqVals) ? min($acqVals) : 0;
                         $maxAcq = count($acqVals) ? max($acqVals) : 100;
                         $rngAcq = max(1e-6, $maxAcq - $minAcq);
                         
                         $minLife = count($lifeVals) ? min($lifeVals) : 0;
                         $maxLife = count($lifeVals) ? max($lifeVals) : 24;
                         $rngLife = max(1e-6, $maxLife - $minLife);
                         
                         $churnTrends = [
                             ['name'=>'Churn Rate %','data'=>$churnRate,'color'=>'#EF4444','min'=>$minChurn,'max'=>$maxChurn,'range'=>$rngChurn],
                             ['name'=>'Retention Rate %','data'=>$retentionRate,'color'=>'#10B981','min'=>$minRet,'max'=>$maxRet,'range'=>$rngRet],
                             ['name'=>'Acquisition Rate %','data'=>$acquisitionRate,'color'=>'#3B82F6','min'=>$minAcq,'max'=>$maxAcq,'range'=>$rngAcq],
                             ['name'=>'Avg Customer Lifetime (months)','data'=>$customerLifetime,'color'=>'#F59E0B','min'=>$minLife,'max'=>$maxLife,'range'=>$rngLife],
                         ];
                     }

                     /* Customer Lifetime Value (CLV) - Most valuable for business decisions */
                     $clvLabels = $dateLabels;
                     $clvTrends = [];
                     if($hasData){
                         $byMonth = $rfmData->groupBy(fn($r)=> \Carbon\Carbon::parse($r->date)->format('Y-m'));
                         
                         // Calculate CLV = Monetary Score × Frequency Score × 10 (scaled for visibility)
                         $clv = $allMonths->map(fn($m)=> !isset($byMonth[$m]) ? null : 
                             round((float)$byMonth[$m]->avg('m_score') * max(1, (float)$byMonth[$m]->avg('f_score')) * 10, 2)
                         )->toArray();
                         
                         // Calculate Average Order Value (AOV) = Monetary Score × 100
                         $aov = $allMonths->map(fn($m)=> !isset($byMonth[$m]) ? null : 
                             round((float)$byMonth[$m]->avg('m_score') * 100, 2)
                         )->toArray();
                         
                         // Calculate Customer Retention Rate (based on frequency stability)
                         $retention = $allMonths->map(fn($m)=> !isset($byMonth[$m]) ? null : 
                             round(min(100, max(0, (float)$byMonth[$m]->avg('f_score') * 10)), 1)
                         )->toArray();
                         
                         // Calculate ranges for scaling
                         $clvVals = array_values(array_filter($clv, fn($v)=> $v !== null));
                         $aovVals = array_values(array_filter($aov, fn($v)=> $v !== null));
                         $retVals = array_values(array_filter($retention, fn($v)=> $v !== null));
                         
                         $minCLV = count($clvVals) ? min($clvVals) : 0;
                         $maxCLV = count($clvVals) ? max($clvVals) : 1;
                         $rngCLV = max(1e-6, $maxCLV - $minCLV);
                         
                         $minAOV = count($aovVals) ? min($aovVals) : 0;
                         $maxAOV = count($aovVals) ? max($aovVals) : 1;
                         $rngAOV = max(1e-6, $maxAOV - $minAOV);
                         
                         $minRet = count($retVals) ? min($retVals) : 0;
                         $maxRet = count($retVals) ? max($retVals) : 100;
                         $rngRet = max(1e-6, $maxRet - $minRet);
                         
                         $clvTrends = [
                             ['name'=>'Customer Lifetime Value','data'=>$clv,'color'=>'#10B981','min'=>$minCLV,'max'=>$maxCLV,'range'=>$rngCLV],
                             ['name'=>'Average Order Value','data'=>$aov,'color'=>'#F59E0B','min'=>$minAOV,'max'=>$maxAOV,'range'=>$rngAOV],
                             ['name'=>'Retention Rate %','data'=>$retention,'color'=>'#8B5CF6','min'=>$minRet,'max'=>$maxRet,'range'=>$rngRet],
                         ];
                     }

                     /* Customer Segmentation Distribution - Essential for business strategy */
                     $segmentationLabels = $dateLabels;
                     $segmentationTrends = [];
                     if($hasData){
                         // Use the new advanced segmentation method
                         try {
                             $controller = new \App\Http\Controllers\RfmAnalysisController();
                             $advancedData = $controller->getAdvancedSegmentAnalysis(
                                 $user->id, 
                                 $activeConnection->tenant_id,
                                 null, // from date (optional)
                                 null  // to date (optional)
                             );
                             
                             // Use the percentile-based monthly data
                             $monthlyData = $advancedData['monthlyData'];
                             
                             // Convert to the format expected by the chart
                             $allMonths = collect($monthlyData)->keys()->sort()->values();
                             $segmentationLabels = $allMonths->map(fn($m) => \Carbon\Carbon::createFromFormat('Y-m', $m)->format('M Y'))->toArray();
                             
                             // Define colors for segments
                             $colors = [
                                 'Champions' => '#10B981',
                                 'Loyal Customers' => '#3B82F6', 
                                 'At Risk' => '#F59E0B',
                                 "Can't Lose" => '#EF4444',
                                 'Lost' => '#6B7280'
                             ];
                             
                             foreach($colors as $segmentName => $color) {
                                 $segmentData = $allMonths->map(function($month) use ($monthlyData, $segmentName) {
                                     return $monthlyData[$month][$segmentName] ?? 0;
                                 })->toArray();
                                 
                                 // Apply smoothing if needed (3-month average)
                                 $smoothedData = [];
                                 for($i = 0; $i < count($segmentData); $i++) {
                                     if($i < 2) {
                                         $smoothedData[] = $segmentData[$i];
                                     } else {
                                         $avg = ($segmentData[$i] + $segmentData[$i-1] + $segmentData[$i-2]) / 3;
                                         $smoothedData[] = round($avg, 1);
                                     }
                                 }
                                 
                                 $segVals = array_values(array_filter($smoothedData, fn($v)=> $v !== null && $v > 0));
                                 $minSeg = count($segVals) ? min($segVals) : 0;
                                 $maxSeg = count($segVals) ? max($segVals) : 1;
                                 $rngSeg = max(1e-6, $maxSeg - $minSeg);
                                 
                                 $segmentationTrends[] = [
                                     'name' => $segmentName,
                                     'data' => $smoothedData,
                                     'color' => $color,
                                     'min' => $minSeg,
                                     'max' => $maxSeg,
                                     'range' => $rngSeg
                                 ];
                             }
                             
                         } catch (Exception $e) {
                             // Fallback to simple RFM score-based segmentation with better error handling
                             $byMonth = $rfmData->groupBy(fn($r)=> \Carbon\Carbon::parse($r->date)->format('Y-m'));
                             
                             $segments = [
                                 'Champions' => ['min' => 8, 'color' => '#10B981'],
                                 'Loyal Customers' => ['min' => 6, 'max' => 7, 'color' => '#3B82F6'],
                                 'At Risk' => ['min' => 4, 'max' => 5, 'color' => '#F59E0B'],
                                 "Can't Lose" => ['min' => 2, 'max' => 3, 'color' => '#EF4444'],
                                 'Lost' => ['max' => 1, 'color' => '#6B7280']
                             ];
                             
                             foreach($segments as $segmentName => $criteria) {
                                 $segmentData = $allMonths->map(function($m) use ($byMonth, $criteria) {
                                     if (!isset($byMonth[$m])) return null;
                                     
                                     $count = $byMonth[$m]->filter(function($row) use ($criteria) {
                                         if (isset($criteria['min']) && isset($criteria['max'])) {
                                             return $row->rfm_score >= $criteria['min'] && $row->rfm_score <= $criteria['max'];
                                         } elseif (isset($criteria['min'])) {
                                             return $row->rfm_score >= $criteria['min'];
                                         } else {
                                             return $row->rfm_score <= $criteria['max'];
                                         }
                                     })->count();
                                     
                                     return $count > 0 ? $count : 0;
                                 })->toArray();
                                 
                                 $segVals = array_values(array_filter($segmentData, fn($v)=> $v !== null && $v > 0));
                                 $minSeg = count($segVals) ? min($segVals) : 0;
                                 $maxSeg = count($segVals) ? max($segVals) : 1;
                                 $rngSeg = max(1e-6, $maxSeg - $minSeg);
                                 
                                 $segmentationTrends[] = [
                                     'name' => $segmentName,
                                     'data' => $segmentData,
                                     'color' => $criteria['color'],
                                     'min' => $minSeg,
                                     'max' => $maxSeg,
                                     'range' => $rngSeg
                                 ];
                             }
                         }
                     }

                     $initialTopN = min(12, count($companies));
                @endphp

                {{-- Simple controls --}}
                <div class="controls">
                    <div class="pill">RFM Analysis Dashboard</div>
                    <div class="pill">Data points: {{ $hasData ? $rfmData->count() : 0 }}</div>
                </div>

                {{-- ============ TAB 1: Company Trends ============ --}}
                <div id="tab-content-company-trend" class="tab-content">
                    <div class="chart-shell">
                        <div class="toolbar">
                            <div class="pill">Companies: {{ count($companies) }}</div>
                            <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
                                <label style="display:flex;align-items:center;gap:.35rem" class="pill">
                                    <input id="chk-benchmark" type="checkbox" checked> Benchmark
                                </label>
                                <button class="pill" onclick="toggleAll('company',true)" type="button">Show all</button>
                                <button class="pill" onclick="toggleAll('company',false)" type="button">Hide all</button>
                                <button class="pill" onclick="exportVisibleCSV()" type="button">Export visible CSV</button>
                                <span class="pill">Tip: Alt/⌥-Click legend to solo</span>
                            </div>
                        </div>

                        <div class="chart-area" id="company-area">
                            <div class="y-labels">
                                <div style="position:absolute;top:0;right:0">{{ number_format($maxV,1) }}</div>
                                <div style="position:absolute;top:25%;right:0">{{ number_format($maxV-$rng*.25,1) }}</div>
                                <div style="position:absolute;top:50%;right:0">{{ number_format($maxV-$rng*.5,1) }}</div>
                                <div style="position:absolute;top:75%;right:0">{{ number_format($maxV-$rng*.75,1) }}</div>
                                <div style="position:absolute;bottom:0;right:0">{{ number_format($minV,1) }}</div>
                            </div>

                            {{-- Points + Paths --}}
                            <div class="data-points" style="position:absolute;inset:0;">
                                @php $nL = max(1, count($dateLabels)-1); @endphp

                                {{-- BENCHMARK dashed line (draw first so series overlay it) --}}
                                @php
                                    $bpts=[]; for($i=0;$i<count($benchmark);$i++){ $v=$benchmark[$i]; if($v!==null){ $x=($i/$nL)*100; $y=100-max(0,min(100,(($v-$minV)/$rng)*100)); $bpts[]=[$x,$y]; } }
                                    $bpath=''; if(count($bpts)){ $bpath = "M {$bpts[0][0]} {$bpts[0][1]}"; for($j=1;$j<count($bpts);$j++){ $bpath .= " L {$bpts[$j][0]} {$bpts[$j][1]}"; } }
                                @endphp
                                @if($bpath!=='')
                                    <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="chart-svg">
                                        <path d="{{ $bpath }}" class="line benchmark" stroke="#9CA3AF" data-kind="company" data-index="-1" id="benchmark-line"/>
                                    </svg>
                                @endif

                                {{-- Series --}}
                                @foreach($companies as $idx => $c)
                                    @php
                                        $series = $c['data']; $color = $c['color'];
                                        $n = count($series); $den = max(1, $n - 1);
                                        $visibleByDefault = $idx < $initialTopN;
                                        $pts=[]; for($i=0;$i<$n;$i++){ $v=$series[$i]; if($v!==null){ $x=($i/$nL)*100; $y=100-max(0,min(100,(($v-$minV)/$rng)*100)); $pts[]=[$x,$y,$i,$v]; } }
                                        $path=''; if(count($pts)){ $path="M {$pts[0][0]} {$pts[0][1]}"; for($j=1;$j<count($pts);$j++){ $path.=" L {$pts[$j][0]} {$pts[$j][1]}"; } }
                                    @endphp

                                    {{-- Path --}}
                                    <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="chart-svg">
                                        @if($path!=='')
                                            <path d="{{ $path }}" class="line {{ $visibleByDefault?'':'muted' }}" stroke="{{ $color }}" data-kind="company" data-index="{{ $idx }}"/>
                                        @endif
                                    </svg>

                                    {{-- Points --}}
                                    <div class="company-group" data-kind="company" data-index="{{ $idx }}" data-name="{{ e($c['name']) }}" data-visible="{{ $visibleByDefault?'1':'0' }}">
                                        @foreach($pts as [$x,$y,$i,$val])
                                            <div class="point {{ $visibleByDefault?'':'muted' }}"
                                                 style="left:{{ $x }}%;top:{{ $y }}%;background:{{ $color }}"
                                                 data-kind="company" data-index="{{ $idx }}"
                                                 data-name="{{ e($c['name']) }}" data-date="{{ $dateLabels[$i] ?? '' }}" data-value="{{ number_format($val,2) }}"></div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>

                            <div class="x-labels">
                                @for($i=0;$i<count($dateLabels);$i++) @php $x=($i/max(1,count($dateLabels)-1))*100; @endphp
                                    <div style="position:absolute;left:{{ $x }}%;transform:translateX(-50%)">{{ $dateLabels[$i] }}</div>
                                @endfor
                            </div>

                            <div class="grid"><div style="top:25%"></div><div style="top:50%"></div><div style="top:75%"></div></div>
                        </div>

                        {{-- Search + Legend (moved down, no overlap) --}}
                        <div class="legend-search">
                            <input id="legend-filter" placeholder="Search company…" oninput="filterLegend(this.value)">
                            <span class="pill">Showing top {{ $initialTopN }} by RFM Score</span>
                        </div>
                        <div class="legend-panel">
                            <div class="legend-wrap">
                                <div class="legend-item" data-kind="company" data-index="-1" onclick="toggleBenchmark()" title="Toggle benchmark">
                                    <div class="swatch" style="background:#9CA3AF"></div><span class="text-gray-400">Benchmark (avg)</span>
                                </div>
                                @foreach($companies as $idx => $c)
                                    <div class="legend-item {{ $idx < $initialTopN ? '' : 'muted' }}"
                                         data-kind="company" data-index="{{ $idx }}" data-label="{{ Str::lower($c['name']) }}"
                                         onclick="legendToggle(event, 'company', {{ $idx }})" title="Click to toggle • Alt/⌥ to solo">
                                        <div class="swatch" style="background:{{ $c['color'] }}"></div>
                                        <span class="text-gray-400">{{ $c['name'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============ TAB 2 + TAB 3 (same as before, now with thin, clean lines) ============ --}}
                {{-- RFM Components --}}
                <div id="tab-content-rfm-breakdown" class="tab-content" style="display:none;">
                    <div class="chart-shell">
                        <div class="toolbar">
                            <div class="pill">Components: 3</div>
                            <div>
                                <button class="pill" onclick="toggleAll('comp',true)" type="button">Show all</button>
                                <button class="pill" onclick="toggleAll('comp',false)" type="button">Hide all</button>
                            </div>
                        </div>

                        <div class="chart-area">
                            <div class="y-labels">
                                <div style="position:absolute;top:0;right:0">10.0</div>
                                <div style="position:absolute;top:25%;right:0">7.5</div>
                                <div style="position:absolute;top:50%;right:0">5.0</div>
                                <div style="position:absolute;top:75%;right:0">2.5</div>
                                <div style="position:absolute;bottom:0;right:0">0.0</div>
                            </div>

                            <div class="data-points" style="position:absolute;inset:0;">
                                @php $nL2=max(1,count($dateLabels)-1); @endphp
                                @foreach($rfmComponents as $idx=>$comp)
                                    @php $series=$comp['data']; $color=$comp['color']; $n=count($series);
                                         $pts=[]; for($i=0;$i<$n;$i++){ $v=$series[$i]; if($v!==null){ $x=($i/$nL2)*100; $y=100-max(0,min(100,($v/10)*100)); $pts[]=[$x,$y,$i,$v]; } }
                                         $path=''; if(count($pts)){ $path="M {$pts[0][0]} {$pts[0][1]}"; for($j=1;$j<count($pts);$j++){ $path.=" L {$pts[$j][0]} {$pts[$j][1]}"; } }
                                    @endphp
                                    <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="chart-svg">
                                        @if($path!=='') <path d="{{ $path }}" class="line" stroke="{{ $color }}" data-kind="comp" data-index="{{ $idx }}"/> @endif
                                    </svg>
                                    <div class="comp-group" data-kind="comp" data-index="{{ $idx }}" data-visible="1" data-name="{{ e($comp['name']) }}">
                                        @foreach($pts as [$x,$y,$i,$val])
                                            <div class="point" style="left:{{ $x }}%;top:{{ $y }}%;background:{{ $color }}"
                                                 data-kind="comp" data-index="{{ $idx }}" data-name="{{ e($comp['name']) }}"
                                                 data-date="{{ $dateLabels[$i] ?? '' }}" data-value="{{ number_format($val,2) }}"></div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>

                            <div class="x-labels">
                                @for($i=0;$i<count($dateLabels);$i++) @php $x=($i/$nL2)*100; @endphp
                                    <div style="position:absolute;left:{{ $x }}%;transform:translateX(-50%)">{{ $dateLabels[$i] }}</div>
                                @endfor
                            </div>

                            <div class="grid"><div style="top:25%"></div><div style="top:50%"></div><div style="top:75%"></div></div>
                        </div>

                        <div class="legend-panel">
                            <div class="legend-wrap">
                                @foreach($rfmComponents as $idx=>$comp)
                                    <div class="legend-item" data-kind="comp" data-index="{{ $idx }}"
                                         onclick="legendToggle(event,'comp',{{ $idx }})">
                                        <div class="swatch" style="background:{{ $comp['color'] }}"></div>
                                        <span class="text-gray-400">{{ $comp['name'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                                 {{-- Revenue --}}
                 <div id="tab-content-revenue-trend" class="tab-content" style="display:none;">
                     <div class="chart-shell">
                         <div class="toolbar">
                             <div class="pill">Independent scales</div>
                             <div>
                                 <button class="pill" onclick="toggleAll('rev',true)" type="button">Show all</button>
                                 <button class="pill" onclick="toggleAll('rev',false)" type="button">Hide all</button>
                             </div>
                         </div>

                         <div class="chart-area">
                             <div class="y-labels">
                                 @if(count($revenueTrends))
                                     @php $leftMax=max(array_map(fn($t)=>$t['max'],$revenueTrends));
                                          $leftMin=min(array_map(fn($t)=>$t['min'],$revenueTrends));
                                          $leftR=max(1e-6,$leftMax-$leftMin); @endphp
                                     <div style="position:absolute;top:0;right:0">{{ number_format($leftMax,0) }}</div>
                                     <div style="position:absolute;top:25%;right:0">{{ number_format($leftMax-$leftR*.25,0) }}</div>
                                     <div style="position:absolute;top:50%;right:0">{{ number_format($leftMax-$leftR*.5,0) }}</div>
                                     <div style="position:absolute;top:75%;right:0">{{ number_format($leftMax-$leftR*.75,0) }}</div>
                                     <div style="position:absolute;bottom:0;right:0">{{ number_format($leftMin,0) }}</div>
                                 @endif
                             </div>

                             <div class="data-points" style="position:absolute;inset:0;">
                                 @php $nL3=max(1,count($revenueLabels)-1); @endphp
                                 @foreach($revenueTrends as $idx=>$t)
                                     @php $series=$t['data']; $color=$t['color']; $tMin=$t['min']; $tRange=$t['range'];
                                          $pts=[]; for($i=0;$i<count($series);$i++){ $v=$series[$i]; if($v!==null){ $x=($i/$nL3)*100; $y=100-max(0,min(100,(($v-$tMin)/$tRange)*100)); $pts[]=[$x,$y,$i,$v]; } }
                                          $path=''; if(count($pts)){ $path="M {$pts[0][0]} {$pts[0][1]}"; for($j=1;$j<count($pts);$j++){ $path.=" L {$pts[$j][0]} {$pts[$j][1]}"; } }
                                     @endphp
                                     <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="chart-svg">
                                         @if($path!=='') <path d="{{ $path }}" class="line" stroke="{{ $color }}" data-kind="rev" data-index="{{ $idx }}"/> @endif
                                     </svg>
                                     <div class="rev-group" data-kind="rev" data-index="{{ $idx }}" data-visible="1" data-name="{{ e($t['name']) }}">
                                         @foreach($pts as [$x,$y,$i,$val])
                                             <div class="point" style="left:{{ $x }}%;top:{{ $y }}%;background:{{ $color }}"
                                                  data-kind="rev" data-index="{{ $idx }}" data-name="{{ e($t['name']) }}"
                                                  data-date="{{ $revenueLabels[$i] ?? '' }}" data-value="{{ number_format($val,0) }}"></div>
                                         @endforeach
                                     </div>
                                 @endforeach
                             </div>

                             <div class="x-labels">
                                 @for($i=0;$i<count($revenueLabels);$i++) @php $x=($i/$nL3)*100; @endphp
                                     <div style="position:absolute;left:{{ $x }}%;transform:translateX(-50%)">{{ $revenueLabels[$i] }}</div>
                                 @endfor
                             </div>

                             <div class="grid"><div style="top:25%"></div><div style="top:50%"></div><div style="top:75%"></div></div>
                         </div>

                         <div class="legend-panel">
                             <div class="legend-wrap">
                                 @foreach($revenueTrends as $idx=>$t)
                                     <div class="legend-item" data-kind="rev" data-index="{{ $idx }}"
                                          onclick="legendToggle(event,'rev',{{ $idx }})">
                                         <div class="swatch" style="background:{{ $t['color'] }}"></div>
                                         <span class="text-gray-400">{{ $t['name'] }}</span>
                                     </div>
                                 @endforeach
                             </div>
                         </div>
                     </div>
                 </div>

                 {{-- Customer Lifetime Value --}}
                 <div id="tab-content-clv-trend" class="tab-content" style="display:none;">
                     <div class="chart-shell">
                         <div class="toolbar">
                             <div class="pill">Customer Value Metrics</div>
                             <div>
                                 <button class="pill" onclick="toggleAll('clv',true)" type="button">Show all</button>
                                 <button class="pill" onclick="toggleAll('clv',false)" type="button">Hide all</button>
                             </div>
                         </div>

                         <div class="chart-area">
                             <div class="y-labels">
                                 @if(count($clvTrends))
                                     @php $leftMax=max(array_map(fn($t)=>$t['max'],$clvTrends));
                                          $leftMin=min(array_map(fn($t)=>$t['min'],$clvTrends));
                                          $leftR=max(1e-6,$leftMax-$leftMin); @endphp
                                     <div style="position:absolute;top:0;right:0">{{ number_format($leftMax,1) }}</div>
                                     <div style="position:absolute;top:25%;right:0">{{ number_format($leftMax-$leftR*.25,1) }}</div>
                                     <div style="position:absolute;top:50%;right:0">{{ number_format($leftMax-$leftR*.5,1) }}</div>
                                     <div style="position:absolute;top:75%;right:0">{{ number_format($leftMax-$leftR*.75,1) }}</div>
                                     <div style="position:absolute;bottom:0;right:0">{{ number_format($leftMin,1) }}</div>
                                 @endif
                             </div>

                             <div class="data-points" style="position:absolute;inset:0;">
                                 @php $nL4=max(1,count($clvLabels)-1); @endphp
                                 @foreach($clvTrends as $idx=>$t)
                                     @php $series=$t['data']; $color=$t['color']; $tMin=$t['min']; $tRange=$t['range'];
                                          $pts=[]; for($i=0;$i<count($series);$i++){ $v=$series[$i]; if($v!==null){ $x=($i/$nL4)*100; $y=100-max(0,min(100,(($v-$tMin)/$tRange)*100)); $pts[]=[$x,$y,$i,$v]; } }
                                          $path=''; if(count($pts)){ $path="M {$pts[0][0]} {$pts[0][1]}"; for($j=1;$j<count($pts);$j++){ $path.=" L {$pts[$j][0]} {$pts[$j][1]}"; } }
                                     @endphp
                                     <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="chart-svg">
                                         @if($path!=='') <path d="{{ $path }}" class="line" stroke="{{ $color }}" data-kind="clv" data-index="{{ $idx }}"/> @endif
                                     </svg>
                                     <div class="clv-group" data-kind="clv" data-index="{{ $idx }}" data-visible="1" data-name="{{ e($t['name']) }}">
                                         @foreach($pts as [$x,$y,$i,$val])
                                             <div class="point" style="left:{{ $x }}%;top:{{ $y }}%;background:{{ $color }}"
                                                  data-kind="clv" data-index="{{ $idx }}" data-name="{{ e($t['name']) }}"
                                                  data-date="{{ $clvLabels[$i] ?? '' }}" data-value="{{ number_format($val,1) }}"></div>
                                         @endforeach
                                     </div>
                                 @endforeach
                             </div>

                             <div class="x-labels">
                                 @for($i=0;$i<count($clvLabels);$i++) @php $x=($i/$nL4)*100; @endphp
                                     <div style="position:absolute;left:{{ $x }}%;transform:translateX(-50%)">{{ $clvLabels[$i] }}</div>
                                 @endfor
                             </div>

                             <div class="grid"><div style="top:25%"></div><div style="top:50%"></div><div style="top:75%"></div></div>
                         </div>

                         <div class="legend-panel">
                             <div class="legend-wrap">
                                 @foreach($clvTrends as $idx=>$t)
                                     <div class="legend-item" data-kind="clv" data-index="{{ $idx }}"
                                          onclick="legendToggle(event,'clv',{{ $idx }})">
                                         <div class="swatch" style="background:{{ $t['color'] }}"></div>
                                         <span class="text-gray-400">{{ $t['name'] }}</span>
                                     </div>
                                 @endforeach
                             </div>
                                                  </div>
                     </div>
                 </div>

                 {{-- Customer Segmentation --}}
                 <div id="tab-content-segmentation" class="tab-content" style="display:none;">
                     <div class="chart-shell">
                         <div class="toolbar">
                             <div class="pill">Customer Segments</div>
                             <div>
                                 <button class="pill" onclick="toggleAll('seg',true)" type="button">Show all</button>
                                 <button class="pill" onclick="toggleAll('seg',false)" type="button">Hide all</button>
                             </div>
                         </div>

                         <div class="chart-area">
                             <div class="y-labels">
                                 @if(count($segmentationTrends))
                                     @php $leftMax=max(array_map(fn($t)=>$t['max'],$segmentationTrends));
                                          $leftMin=min(array_map(fn($t)=>$t['min'],$segmentationTrends));
                                          $leftR=max(1e-6,$leftMax-$leftMin); @endphp
                                     <div style="position:absolute;top:0;right:0">{{ number_format($leftMax,0) }}</div>
                                     <div style="position:absolute;top:25%;right:0">{{ number_format($leftMax-$leftR*.25,0) }}</div>
                                     <div style="position:absolute;top:50%;right:0">{{ number_format($leftMax-$leftR*.5,0) }}</div>
                                     <div style="position:absolute;top:75%;right:0">{{ number_format($leftMax-$leftR*.75,0) }}</div>
                                     <div style="position:absolute;bottom:0;right:0">{{ number_format($leftMin,0) }}</div>
                                 @endif
                             </div>

                             <div class="data-points" style="position:absolute;inset:0;">
                                 @php $nL5=max(1,count($segmentationLabels)-1); @endphp
                                 @foreach($segmentationTrends as $idx=>$t)
                                     @php $series=$t['data']; $color=$t['color']; $tMin=$t['min']; $tRange=$t['range'];
                                          $pts=[]; for($i=0;$i<count($series);$i++){ $v=$series[$i]; if($v!==null){ $x=($i/$nL5)*100; $y=100-max(0,min(100,(($v-$tMin)/$tRange)*100)); $pts[]=[$x,$y,$i,$v]; } }
                                          $path=''; if(count($pts)){ $path="M {$pts[0][0]} {$pts[0][1]}"; for($j=1;$j<count($pts);$j++){ $path.=" L {$pts[$j][0]} {$pts[$j][1]}"; } }
                                     @endphp
                                     <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="chart-svg">
                                         @if($path!=='') <path d="{{ $path }}" class="line" stroke="{{ $color }}" data-kind="seg" data-index="{{ $idx }}"/> @endif
                                     </svg>
                                     <div class="seg-group" data-kind="seg" data-index="{{ $idx }}" data-visible="1" data-name="{{ e($t['name']) }}">
                                         @foreach($pts as [$x,$y,$i,$val])
                                             <div class="point" style="left:{{ $x }}%;top:{{ $y }}%;background:{{ $color }}"
                                                  data-kind="seg" data-index="{{ $idx }}" data-name="{{ e($t['name']) }}"
                                                  data-date="{{ $segmentationLabels[$i] ?? '' }}" data-value="{{ number_format($val,0) }}"></div>
                                         @endforeach
                                     </div>
                                 @endforeach
                             </div>

                             <div class="x-labels">
                                 @for($i=0;$i<count($segmentationLabels);$i++) @php $x=($i/$nL5)*100; @endphp
                                     <div style="position:absolute;left:{{ $x }}%;transform:translateX(-50%)">{{ $segmentationLabels[$i] }}</div>
                                 @endfor
                             </div>

                             <div class="grid"><div style="top:25%"></div><div style="top:50%"></div><div style="top:75%"></div></div>
                         </div>

                         <div class="legend-panel">
                             <div class="legend-wrap">
                                 @foreach($segmentationTrends as $idx=>$t)
                                     <div class="legend-item" data-kind="seg" data-index="{{ $idx }}"
                                          onclick="legendToggle(event,'seg',{{ $idx }})">
                                         <div class="swatch" style="background:{{ $t['color'] }}"></div>
                                         <span class="text-gray-400">{{ $t['name'] }}</span>
                                     </div>
                                 @endforeach
                             </div>
                         </div>
                         
                     </div>
                 </div>

                 {{-- Churn & Retention Analysis --}}
                 <div id="tab-content-churn-analysis" class="tab-content" style="display:none;">
                     <div class="chart-shell">
                         <div class="toolbar">
                             <div class="pill">Customer Health Metrics</div>
                             <div>
                                 <button class="pill" onclick="toggleAll('churn',true)" type="button">Show all</button>
                                 <button class="pill" onclick="toggleAll('churn',false)" type="button">Hide all</button>
                             </div>
                         </div>

                         <div class="chart-area">
                             <div class="y-labels">
                                 @if(count($churnTrends))
                                     @php $leftMax=max(array_map(fn($t)=>$t['max'],$churnTrends));
                                          $leftMin=min(array_map(fn($t)=>$t['min'],$churnTrends));
                                          $leftR=max(1e-6,$leftMax-$leftMin); @endphp
                                     <div style="position:absolute;top:0;right:0">{{ number_format($leftMax,0) }}</div>
                                     <div style="position:absolute;top:25%;right:0">{{ number_format($leftMax-$leftR*.25,0) }}</div>
                                     <div style="position:absolute;top:50%;right:0">{{ number_format($leftMax-$leftR*.5,0) }}</div>
                                     <div style="position:absolute;top:75%;right:0">{{ number_format($leftMax-$leftR*.75,0) }}</div>
                                     <div style="position:absolute;bottom:0;right:0">{{ number_format($leftMin,0) }}</div>
                                 @endif
                             </div>

                             <div class="data-points" style="position:absolute;inset:0;">
                                 @php $nL6=max(1,count($churnLabels)-1); @endphp
                                 @foreach($churnTrends as $idx=>$t)
                                     @php $series=$t['data']; $color=$t['color']; $tMin=$t['min']; $tRange=$t['range'];
                                          $pts=[]; for($i=0;$i<count($series);$i++){ $v=$series[$i]; if($v!==null){ $x=($i/$nL6)*100; $y=100-max(0,min(100,(($v-$tMin)/$tRange)*100)); $pts[]=[$x,$y,$i,$v]; } }
                                          $path=''; if(count($pts)){ $path="M {$pts[0][0]} {$pts[0][1]}"; for($j=1;$j<count($pts);$j++){ $path.=" L {$pts[$j][0]} {$pts[$j][1]}"; } }
                                     @endphp
                                     <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="chart-svg">
                                         @if($path!=='') <path d="{{ $path }}" class="line" stroke="{{ $color }}" data-kind="churn" data-index="{{ $idx }}"/> @endif
                                     </svg>
                                     <div class="churn-group" data-kind="churn" data-index="{{ $idx }}" data-visible="1" data-name="{{ e($t['name']) }}">
                                         @foreach($pts as [$x,$y,$i,$val])
                                             <div class="point" style="left:{{ $x }}%;top:{{ $y }}%;background:{{ $color }}"
                                                  data-kind="churn" data-index="{{ $idx }}" data-name="{{ e($t['name']) }}"
                                                  data-date="{{ $churnLabels[$i] ?? '' }}" data-value="{{ number_format($val,1) }}"></div>
                                         @endforeach
                                     </div>
                                 @endforeach
                             </div>

                             <div class="x-labels">
                                 @for($i=0;$i<count($churnLabels);$i++) @php $x=($i/$nL6)*100; @endphp
                                     <div style="position:absolute;left:{{ $x }}%;transform:translateX(-50%)">{{ $churnLabels[$i] }}</div>
                                 @endfor
                             </div>

                             <div class="grid"><div style="top:25%"></div><div style="top:50%"></div><div style="top:75%"></div></div>
                         </div>

                         <div class="legend-panel">
                             <div class="legend-wrap">
                                 @foreach($churnTrends as $idx=>$t)
                                     <div class="legend-item" data-kind="churn" data-index="{{ $idx }}"
                                          onclick="legendToggle(event,'churn',{{ $idx }})">
                                         <div class="swatch" style="background:{{ $t['color'] }}"></div>
                                         <span class="text-gray-400">{{ $t['name'] }}</span>
                                     </div>
                                 @endforeach
                             </div>
                         </div>
                     </div>
                 </div>

                 {{-- ===== JS ===== --}}
                <div id="chart-tip" class="tip"></div>
                <script>
                    // Tabs
                    function showTab(name){
                        document.querySelectorAll('.tab-content').forEach(c=>c.style.display='none');
                        document.querySelectorAll('.tab-button').forEach(b=>{b.classList.remove('active','border-blue-500','text-blue-600','dark:text-blue-400');b.classList.add('border-transparent','text-gray-500','dark:text-gray-400');});
                        document.getElementById('tab-content-'+name).style.display='block';
                        const btn=document.getElementById('tab-'+name);btn.classList.add('active','border-blue-500','text-blue-600','dark:text-blue-400');btn.classList.remove('border-transparent','text-gray-500','dark:text-gray-400');
                    }

                                         // Simple functions
                     function setDateInputs(s,e){}
                     function presetMonths(n){}
                     function presetYTD(){}

                    // Tooltip / highlight
                    const tip=document.getElementById('chart-tip');
                    const showTip=(e)=>{const t=e.target;if(!t.classList.contains('point'))return;tip.innerHTML=`<strong>${t.dataset.name}</strong><br>${t.dataset.date}<br><span style="opacity:.85">${t.dataset.value}</span>`;tip.style.display='block';tip.style.left=(e.clientX+12)+'px';tip.style.top=(e.clientY-12)+'px';highlight(t.dataset.kind,t.dataset.index,true);};
                    const moveTip=(e)=>{if(tip.style.display==='block'){tip.style.left=(e.clientX+12)+'px';tip.style.top=(e.clientY-12)+'px';}}
                    const hideTip=(e)=>{const t=e.target;if(!t.classList.contains('point'))return;tip.style.display='none';highlight(t.dataset.kind,t.dataset.index,false);};
                    document.addEventListener('mousemove',moveTip);document.addEventListener('mouseover',showTip,true);document.addEventListener('mouseout',hideTip,true);
                                         function highlight(kind,idx,on){
                         const lineSel = kind==='company'?'.line[data-kind="company"][data-index="'+idx+'"]' : kind==='comp'?'.line[data-kind="comp"][data-index="'+idx+'"]' : kind==='rev'?'.line[data-kind="rev"][data-index="'+idx+'"]' : kind==='clv'?'.line[data-kind="clv"][data-index="'+idx+'"]' : kind==='seg'?'.line[data-kind="seg"][data-index="'+idx+'"]' : '.line[data-kind="churn"][data-index="'+idx+'"]';
                         document.querySelectorAll(lineSel).forEach(p=>p.style.strokeWidth=on?'1.5':'1');
                     }

                    // Toggle
                                         function setVisible(kind, idx, visible){
                         const groupSel = kind==='company'?'.company-group':kind==='comp'?'.comp-group':kind==='rev'?'.rev-group':kind==='clv'?'.clv-group':kind==='seg'?'.seg-group':'.churn-group';
                         document.querySelectorAll(`${groupSel}[data-index="${idx}"] .point`).forEach(el=>el.classList.toggle('muted', !visible));
                         document.querySelectorAll(`.line[data-kind="${kind}"][data-index="${idx}"]`).forEach(el=>el.classList.toggle('muted', !visible));
                         const legend=document.querySelector(`.legend-item[data-kind="${kind}"][data-index="${idx}"]`);
                         if(legend) legend.classList.toggle('muted', !visible);
                         document.querySelectorAll(`${groupSel}[data-index="${idx}"]`).forEach(g=>g.dataset.visible=visible?'1':'0');
                     }
                     function isVisible(kind, idx){
                         const groupSel = kind==='company'?'.company-group':kind==='comp'?'.comp-group':kind==='rev'?'.rev-group':kind==='clv'?'.clv-group':kind==='seg'?'.seg-group':'.churn-group';
                         const g=document.querySelector(`${groupSel}[data-index="${idx}"]`); return g?g.dataset.visible==='1':false;
                     }
                    function legendToggle(ev, kind, idx){
                        const solo=ev.altKey||ev.metaKey;
                        if(solo){
                            document.querySelectorAll(`.legend-item[data-kind="${kind}"]`).forEach(li=>{
                                const i=li.dataset.index; setVisible(kind, i, i==idx);
                            });
                        }else setVisible(kind, idx, !isVisible(kind, idx));
                    }
                    function toggleAll(kind, show){
                        document.querySelectorAll(`.legend-item[data-kind="${kind}"]`).forEach(li=>setVisible(kind, li.dataset.index, show));
                    }

                                         // Benchmark toggle
                     function toggleBenchmark(){
                         const p=document.getElementById('benchmark-line'); if(!p) return;
                         const off=p.style.display==='none'; p.style.display=off?'block':'none';
                         const checkbox = document.getElementById('chk-benchmark');
                         if(checkbox) checkbox.checked = off;
                     }
                     document.addEventListener('DOMContentLoaded', function() {
                         const checkbox = document.getElementById('chk-benchmark');
                         if(checkbox) checkbox.addEventListener('change', toggleBenchmark);
                     });

                    // Legend filter
                    function filterLegend(q){
                        const v=(q||'').toLowerCase().trim();
                        document.querySelectorAll('.legend-wrap .legend-item').forEach(li=>{
                            if(li.dataset.index === '-1') return; // keep benchmark
                            const ok=!v || li.dataset.label.includes(v);
                            li.style.display = ok? 'inline-flex' : 'none';
                        });
                    }

                    // Init top-N visibility for companies
                    (function initCompanyTopN(){
                        document.querySelectorAll('.company-group').forEach(g=>setVisible('company', g.dataset.index, g.dataset.visible==='1'));
                    })();

                                         // Export visible series (Company Trends)
                     function exportVisibleCSV(){
                         const rows=[];
                         document.querySelectorAll('.company-group').forEach(g=>{
                             if(g.dataset.visible!=='1') return;
                             const name=g.dataset.name;
                             g.querySelectorAll('.point').forEach(p=>{
                                 rows.push([name, p.dataset.date, p.dataset.value]);
                             });
                         });
                         if(!rows.length){ alert('Nothing visible to export.'); return; }
                         let csv='Company,Date,Value\n'+rows.map(r=>r.map(x=>`"${String(x).replace(/"/g,'""')}"`).join(',')).join('\n');
                         const blob=new Blob([csv],{type:'text/csv;charset=utf-8;'}); 
                         const url=URL.createObjectURL(blob);
                         const a=document.createElement('a'); 
                         a.href=url; 
                         a.download='company-trends.csv'; 
                         a.click(); 
                         URL.revokeObjectURL(url);
                     }

                                         // ESC = show all in the current tab
                     document.addEventListener('keydown', e=>{
                         if(e.key!=='Escape') return;
                         const active = Array.from(document.querySelectorAll('.tab-content')).find(c=>c.style.display!=='none');
                         if(!active) return;
                         if(active.id.includes('company')) toggleAll('company',true);
                         if(active.id.includes('rfm'))     toggleAll('comp',true);
                         if(active.id.includes('revenue')) toggleAll('rev',true);
                         if(active.id.includes('clv'))     toggleAll('clv',true);
                         if(active.id.includes('segmentation')) toggleAll('seg',true);
                         if(active.id.includes('churn-analysis')) toggleAll('churn',true);
                     });

                     // Segment view controls
                     function toggleSegmentView() {
                         const view = document.getElementById('segment-view').value;
                         console.log('Switching to view:', view);
                         // TODO: Implement view switching logic
                         // This would require backend changes to support different data views
                     }

                     function toggleSmoothing() {
                         const smoothing = document.getElementById('segment-smoothing').value;
                         console.log('Applying smoothing:', smoothing);
                         // TODO: Implement smoothing logic
                         // This would apply 3-month moving averages to the data
                     }

                     // Exclude current month
                     document.getElementById('exclude-current').addEventListener('change', function() {
                         console.log('Exclude current month:', this.checked);
                         // TODO: Implement exclude current month logic
                         // This would filter out the current month's data
                     });
                </script>



                {{-- Table + summary (unchanged) --}}
                {{-- ... keep your existing summary table here if you want ... --}}
            </div>
        </div>
    </div>
</x-app-layout>
