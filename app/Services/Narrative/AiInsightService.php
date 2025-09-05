<?php

namespace App\Services\Narrative;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AiInsightService
{
    private string $lastProvider = 'deterministic';
    private ?string $lastError = null;

    public function getLastProvider(): string
    {
        return $this->lastProvider;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function generateInsight(string $section, array $data, ?User $user = null): string
    {
        $prompt = $this->buildPrompt($section, $data);
        
        // Check if OpenAI is configured and enabled
        $apiKey = config('ai.openai.api_key');
        $enabled = config('ai.insights.enabled', true);
        
        if ($apiKey && $enabled) {
            // Try OpenAI first, fallback to deterministic if it fails
            try {
                $text = $this->callOpenAI($prompt);
                $this->lastProvider = 'openai';
                return $text;
            } catch (\Exception $e) {
                Log::warning('OpenAI API call failed: ' . $e->getMessage());
                $this->lastError = $e->getMessage();
                $this->lastProvider = 'deterministic';
                return $this->getDeterministicInsight($section, $data);
            }
        } else {
            // Use deterministic insights if OpenAI not configured
            $this->lastProvider = 'deterministic';
            $this->lastError = $apiKey ? 'AI insights disabled' : 'OpenAI API key not configured';
            return $this->getDeterministicInsight($section, $data);
        }
    }
    
    private function buildPrompt(string $section, array $data): string
    {
        return match($section) {
            'executive-summary' => $this->buildExecutiveSummaryPrompt($data),
            'customer-movement' => $this->buildCustomerMovementPrompt($data),
            'risk-assessment' => $this->buildRiskAssessmentPrompt($data),
            'growth-opportunities' => $this->buildGrowthOpportunitiesPrompt($data),
            'revenue-concentration' => $this->buildRevenueConcentrationPrompt($data),
            'customer-segments' => $this->buildCustomerSegmentsPrompt($data),
            'historical-trends' => $this->buildHistoricalTrendsPrompt($data),
            default => $this->buildGenericPrompt($section, $data),
        };
    }
    
    private function buildExecutiveSummaryPrompt(array $data): string
    {
        $revenue = $data['current_period']['total_revenue'] ?? 0;
        $revenueChange = $data['analysis']['revenue_change'] ?? 0;
        $customers = $data['current_period']['active_customers'] ?? 0;
        $customerChange = $data['analysis']['customer_change'] ?? 0;
        $avgRfm = $data['current_period']['average_rfm'] ?? 0;
        $aov = $data['current_period']['average_order_value'] ?? 0;
        $aovChange = $data['analysis']['aov_change'] ?? 0;
        
        // Get additional context
        $movement = $data['current_period']['customer_movement'] ?? [];
        $retentionRate = $movement['retention_rate'] ?? 0;
        $newCustomers = $movement['new_customers'] ?? 0;
        $lostCustomers = $movement['lost_customers'] ?? 0;
        
        $concentration = $data['concentration'] ?? [];
        $top10Share = $concentration['top_10_share'] ?? 0;
        $customersTo80 = $concentration['customers_to_80_percent'] ?? 0;
        
        return "
        You are a senior business analyst with 15+ years of experience in customer analytics and business strategy. 
        Analyze this comprehensive business data and provide actionable insights:

        **CORE METRICS:**
        - Revenue: £{$revenue} ({$revenueChange}% vs previous period)
        - Active Customers: {$customers} ({$customerChange}% vs previous period)
        - Average RFM Score: {$avgRfm}/10 (customer value indicator)
        - Average Order Value: £{$aov} ({$aovChange}% vs previous period)

        **CUSTOMER DYNAMICS:**
        - Customer Retention Rate: {$retentionRate}%
        - New Customers Acquired: {$newCustomers}
        - Customers Lost: {$lostCustomers}

        **REVENUE CONCENTRATION:**
        - Top 10 Customers: {$top10Share}% of total revenue
        - Customers needed for 80% of revenue: {$customersTo80}

        **ANALYSIS CONTEXT:**
        - RFM Score ranges: 1-3 (low value), 4-6 (medium value), 7-10 (high value)
        - This is a B2B business with recurring revenue model
        - Focus on actionable insights for business growth and risk mitigation

        Provide a comprehensive 3-4 sentence analysis that includes:
        1. What the data reveals about business health
        2. Specific risks or opportunities identified
        3. Recommended immediate actions (be specific)
        4. Strategic implications for the next quarter

        Write in a professional, executive-friendly tone with concrete recommendations.
        ";
    }
    
    private function buildCustomerMovementPrompt(array $data): string
    {
        $movement = $data['current_period']['customer_movement'] ?? [];
        $retained = $movement['retained_customers'] ?? 0;
        $new = $movement['new_customers'] ?? 0;
        $returned = $movement['returned_customers'] ?? 0;
        $lost = $movement['lost_customers'] ?? 0;
        $retentionRate = $movement['retention_rate'] ?? 0;
        
        return "
        You are a customer retention specialist. Analyze this customer movement data:
        
        RETAINED: {$retained} customers
        NEW: {$new} customers
        RETURNED: {$returned} customers
        LOST: {$lost} customers
        RETENTION RATE: {$retentionRate}%
        
        Write a 2-3 sentence insight about customer movement patterns and what they indicate.
        ";
    }
    
    private function buildRiskAssessmentPrompt(array $data): string
    {
        $risks = $data['risk_analysis'] ?? [];
        $riskCount = count($risks);
        $highRiskCount = count(array_filter($risks, fn($r) => ($r['severity'] ?? '') === 'high'));
        $mediumRiskCount = count(array_filter($risks, fn($r) => ($r['severity'] ?? '') === 'medium'));
        
        // Get concentration and customer data
        $concentration = $data['concentration'] ?? [];
        $top10Share = $concentration['top_10_share'] ?? 0;
        $customersTo80 = $concentration['customers_to_80_percent'] ?? 0;
        
        $movement = $data['current_period']['customer_movement'] ?? [];
        $retentionRate = $movement['retention_rate'] ?? 0;
        $lostCustomers = $movement['lost_customers'] ?? 0;
        
        $revenueChange = $data['analysis']['revenue_change'] ?? 0;
        $customerChange = $data['analysis']['customer_change'] ?? 0;
        
        // Build detailed risk context
        $riskDetails = [];
        foreach ($risks as $risk) {
            $riskDetails[] = "- " . ($risk['severity'] ?? 'unknown') . " severity: " . ($risk['title'] ?? 'Unknown risk');
        }
        $riskList = implode("\n", $riskDetails);
        
        return "
        You are a senior risk management consultant with expertise in customer portfolio analysis and business continuity planning.
        Analyze this comprehensive risk profile and provide strategic risk management insights:

        **RISK INVENTORY:**
        Total Risks Identified: {$riskCount}
        - High Severity: {$highRiskCount}
        - Medium Severity: {$mediumRiskCount}

        **SPECIFIC RISKS:**
        {$riskList}

        **BUSINESS CONTEXT:**
        - Revenue Change: {$revenueChange}% (trend indicator)
        - Customer Change: {$customerChange}% (stability indicator)
        - Revenue Concentration: Top 10 customers = {$top10Share}%
        - Customers for 80% revenue: {$customersTo80}
        - Customer Retention Rate: {$retentionRate}%
        - Recently Lost Customers: {$lostCustomers}

        **ANALYSIS REQUIREMENTS:**
        1. Assess the overall risk profile severity and business impact
        2. Identify the most critical risks requiring immediate attention
        3. Evaluate interconnected risks (e.g., concentration + customer loss)
        4. Provide specific, actionable risk mitigation strategies
        5. Suggest monitoring mechanisms and early warning indicators

        Provide a comprehensive 4-5 sentence analysis that prioritizes risks by potential business impact and urgency.
        Focus on practical, implementable risk mitigation strategies that this business can execute immediately.
        ";
    }
    
    private function buildGrowthOpportunitiesPrompt(array $data): string
    {
        $opportunities = $data['opportunities'] ?? [];
        $opportunityCount = count($opportunities);
        $highPriorityCount = count(array_filter($opportunities, fn($o) => ($o['priority'] ?? '') === 'high'));
        
        // Get customer segment data
        $segments = $data['segments'] ?? [];
        $highValue = $segments['high_value']['count'] ?? 0;
        $midValue = $segments['mid_value']['count'] ?? 0;
        $lowValue = $segments['low_value']['count'] ?? 0;
        $atRisk = $segments['at_risk']['count'] ?? 0;
        
        // Get revenue and customer metrics
        $revenueChange = $data['analysis']['revenue_change'] ?? 0;
        $customerChange = $data['analysis']['customer_change'] ?? 0;
        $avgRfm = $data['current_period']['average_rfm'] ?? 0;
        $aov = $data['current_period']['average_order_value'] ?? 0;
        
        // Get movement data
        $movement = $data['current_period']['customer_movement'] ?? [];
        $newCustomers = $movement['new_customers'] ?? 0;
        $returnedCustomers = $movement['returned_customers'] ?? 0;
        
        // Build opportunity details
        $opportunityDetails = [];
        foreach ($opportunities as $opportunity) {
            $opportunityDetails[] = "- " . ($opportunity['title'] ?? 'Unknown opportunity') . " (" . ($opportunity['potential_impact'] ?? 'Unknown impact') . ")";
        }
        $opportunityList = implode("\n", $opportunityDetails);
        
        return "
        You are a senior growth strategist with expertise in customer lifecycle optimization and revenue expansion strategies.
        Analyze this comprehensive growth opportunity landscape and provide strategic growth recommendations:

        **GROWTH OPPORTUNITY INVENTORY:**
        Total Opportunities: {$opportunityCount}
        High Priority: {$highPriorityCount}

        **SPECIFIC OPPORTUNITIES:**
        {$opportunityList}

        **CUSTOMER PORTFOLIO CONTEXT:**
        - High Value Customers: {$highValue} (target for retention & expansion)
        - Mid Value Customers: {$midValue} (upselling potential)
        - Low Value Customers: {$lowValue} (development opportunities)
        - At Risk Customers: {$atRisk} (retention focus)

        **BUSINESS PERFORMANCE:**
        - Revenue Trend: {$revenueChange}% (growth indicator)
        - Customer Base: {$customerChange}% (acquisition indicator)
        - Average RFM Score: {$avgRfm}/10 (customer quality)
        - Average Order Value: £{$aov} (monetization potential)

        **CUSTOMER ACQUISITION:**
        - New Customers: {$newCustomers}
        - Returning Customers: {$returnedCustomers}

        **ANALYSIS REQUIREMENTS:**
        1. Prioritize opportunities by revenue impact potential
        2. Identify quick wins vs. long-term strategic plays
        3. Assess resource requirements and implementation complexity
        4. Evaluate customer segment-specific growth strategies
        5. Consider market conditions and competitive landscape

        Provide a comprehensive 4-5 sentence analysis that prioritizes growth opportunities by potential impact and implementation feasibility.
        Focus on actionable strategies that can drive immediate and sustainable revenue growth.
        ";
    }
    
    private function buildRevenueConcentrationPrompt(array $data): string
    {
        $concentration = $data['concentration'] ?? [];
        $top10Share = $concentration['top_10_share'] ?? 0;
        $top50Share = $concentration['top_50_share'] ?? 0;
        $customersTo80 = $concentration['customers_to_80_percent'] ?? 0;
        
        // Get business context
        $revenueChange = $data['analysis']['revenue_change'] ?? 0;
        $customerChange = $data['analysis']['customer_change'] ?? 0;
        $activeCustomers = $data['current_period']['active_customers'] ?? 0;
        $totalRevenue = $data['current_period']['total_revenue'] ?? 0;
        
        // Determine concentration risk level
        $riskLevel = '';
        if ($top10Share > 90) $riskLevel = 'EXTREME';
        elseif ($top10Share > 80) $riskLevel = 'CRITICAL';
        elseif ($top10Share > 60) $riskLevel = 'HIGH';
        elseif ($top10Share > 40) $riskLevel = 'MODERATE';
        else $riskLevel = 'LOW';
        
        return "
        You are a senior business risk consultant specializing in revenue diversification and customer portfolio optimization.
        Analyze this revenue concentration profile and provide strategic risk management insights:

        **REVENUE CONCENTRATION METRICS:**
        - Top 10 Customers: {$top10Share}% of revenue
        - Top 50 Customers: {$top50Share}% of revenue  
        - Customers for 80% Revenue: {$customersTo80} customers
        - Risk Level: {$riskLevel}

        **BUSINESS CONTEXT:**
        - Total Revenue: £{$totalRevenue}
        - Revenue Trend: {$revenueChange}%
        - Active Customer Base: {$activeCustomers}
        - Customer Change: {$customerChange}%

        **ANALYSIS REQUIREMENTS:**
        1. Assess concentration risk severity and business vulnerability
        2. Evaluate the impact on business stability and growth
        3. Identify specific risks if key customers are lost
        4. Recommend diversification strategies and timeline
        5. Suggest monitoring mechanisms for key account health

        Provide a comprehensive 4-5 sentence analysis that quantifies the concentration risk, explains business implications, 
        and offers specific, actionable strategies for revenue diversification. Focus on immediate risk mitigation and 
        long-term portfolio balance strategies.
        ";
    }
    
    private function buildCustomerSegmentsPrompt(array $data): string
    {
        $segments = $data['segments'] ?? [];
        $highValue = $segments['high_value']['count'] ?? 0;
        $highValueAvg = $segments['high_value']['average_rfm'] ?? 0;
        $midValue = $segments['mid_value']['count'] ?? 0;
        $midValueAvg = $segments['mid_value']['average_rfm'] ?? 0;
        $lowValue = $segments['low_value']['count'] ?? 0;
        $lowValueAvg = $segments['low_value']['average_rfm'] ?? 0;
        $atRisk = $segments['at_risk']['count'] ?? 0;
        $atRiskAvg = $segments['at_risk']['average_rfm'] ?? 0;
        $inactive = $segments['inactive']['count'] ?? 0;
        
        $totalActive = $highValue + $midValue + $lowValue + $atRisk;
        $totalCustomers = $totalActive + $inactive;
        
        // Calculate percentages
        $highValuePct = $totalActive > 0 ? round(($highValue / $totalActive) * 100, 1) : 0;
        $midValuePct = $totalActive > 0 ? round(($midValue / $totalActive) * 100, 1) : 0;
        $lowValuePct = $totalActive > 0 ? round(($lowValue / $totalActive) * 100, 1) : 0;
        $atRiskPct = $totalActive > 0 ? round(($atRisk / $totalActive) * 100, 1) : 0;
        $inactivePct = $totalCustomers > 0 ? round(($inactive / $totalCustomers) * 100, 1) : 0;
        
        // Get business context
        $revenueChange = $data['analysis']['revenue_change'] ?? 0;
        $aov = $data['current_period']['average_order_value'] ?? 0;
        
        return "
        You are a senior customer lifecycle strategist with expertise in value-based segmentation and customer development.
        Analyze this customer portfolio structure and provide strategic customer management insights:

        **CUSTOMER SEGMENT DISTRIBUTION:**
        - High Value (RFM 8-10): {$highValue} customers ({$highValuePct}%) - Avg RFM: {$highValueAvg}
        - Mid Value (RFM 5-7): {$midValue} customers ({$midValuePct}%) - Avg RFM: {$midValueAvg}
        - Low Value (RFM 2-4): {$lowValue} customers ({$lowValuePct}%) - Avg RFM: {$lowValueAvg}
        - At Risk (RFM 0-1): {$atRisk} customers ({$atRiskPct}%) - Avg RFM: {$atRiskAvg}
        - Inactive: {$inactive} customers ({$inactivePct}% of total)

        **PORTFOLIO METRICS:**
        - Total Active Customers: {$totalActive}
        - Total Customer Base: {$totalCustomers}
        - Revenue Trend: {$revenueChange}%
        - Average Order Value: £{$aov}

        **ANALYSIS REQUIREMENTS:**
        1. Evaluate portfolio balance and segment health
        2. Identify segment migration opportunities (low → mid → high value)
        3. Assess retention risks in each segment
        4. Recommend segment-specific strategies for value enhancement
        5. Prioritize segments for investment and development

        Provide a comprehensive 4-5 sentence analysis that evaluates portfolio balance, identifies the highest-opportunity segments, 
        and recommends specific strategies for customer value development. Focus on actionable tactics for segment migration 
        and retention optimization.
        ";
    }
    
    private function buildHistoricalTrendsPrompt(array $data): string
    {
        $trends = $data['historical_trends'] ?? [];
        $trendCount = count($trends);
        
        if (empty($trends)) {
            return "No historical trends data available for analysis.";
        }
        
        // Get first and last periods for comparison
        $firstPeriod = reset($trends);
        $lastPeriod = end($trends);
        
        $firstCustomers = $firstPeriod['total_customers'] ?? 0;
        $lastCustomers = $lastPeriod['total_customers'] ?? 0;
        $firstRfm = $firstPeriod['average_rfm'] ?? 0;
        $lastRfm = $lastPeriod['average_rfm'] ?? 0;
        $firstHighValue = $firstPeriod['high_value_customers'] ?? 0;
        $lastHighValue = $lastPeriod['high_value_customers'] ?? 0;
        $firstAtRisk = $firstPeriod['at_risk_customers'] ?? 0;
        $lastAtRisk = $lastPeriod['at_risk_customers'] ?? 0;
        
        // Calculate changes
        $customerChange = $firstCustomers > 0 ? round((($lastCustomers - $firstCustomers) / $firstCustomers) * 100, 1) : 0;
        $rfmChange = $firstRfm > 0 ? round((($lastRfm - $firstRfm) / $firstRfm) * 100, 1) : 0;
        $highValueChange = $lastHighValue - $firstHighValue;
        $atRiskChange = $lastAtRisk - $firstAtRisk;
        
        // Analyze trend direction
        $customerTrend = $customerChange > 2 ? 'Growing' : ($customerChange < -2 ? 'Declining' : 'Stable');
        $rfmTrend = $rfmChange > 5 ? 'Improving' : ($rfmChange < -5 ? 'Deteriorating' : 'Stable');
        
        // Build period summary
        $periodSummary = [];
        foreach ($trends as $period) {
            $date = $period['period'] ?? 'Unknown';
            $customers = $period['total_customers'] ?? 0;
            $rfm = $period['average_rfm'] ?? 0;
            $periodSummary[] = "{$date}: {$customers} customers, RFM {$rfm}";
        }
        $trendDetails = implode("\n        ", $periodSummary);
        
        return "
        You are a senior business intelligence analyst with expertise in performance trends and predictive analytics.
        Analyze this historical performance data and provide strategic trend insights:

        **TREND ANALYSIS PERIOD:**
        {$trendCount} periods analyzed
        
        **DETAILED TREND DATA:**
        {$trendDetails}

        **KEY CHANGES (First → Last Period):**
        - Customer Base: {$firstCustomers} → {$lastCustomers} ({$customerChange}% change)
        - Average RFM: {$firstRfm} → {$lastRfm} ({$rfmChange}% change)
        - High Value Customers: {$firstHighValue} → {$lastHighValue} ({$highValueChange} change)
        - At Risk Customers: {$firstAtRisk} → {$lastAtRisk} ({$atRiskChange} change)

        **TREND CLASSIFICATION:**
        - Customer Base Trend: {$customerTrend}
        - RFM Quality Trend: {$rfmTrend}

        **ANALYSIS REQUIREMENTS:**
        1. Identify overall performance trajectory and momentum
        2. Assess trend consistency vs. volatility
        3. Evaluate leading indicators for future performance
        4. Identify inflection points or concerning patterns
        5. Predict likely next-period performance based on trends

        Provide a comprehensive 4-5 sentence analysis that interprets the historical trends, identifies the most significant 
        patterns, and offers predictions for future performance. Focus on actionable insights for strategic planning and 
        early warning indicators for management attention.
        ";
    }
    
    private function buildGenericPrompt(string $section, array $data): string
    {
        return "
        You are a business analyst. Review this {$section} data and provide a 2-3 sentence insight about what it means for the business.
        ";
    }
    
    private function getDeterministicInsight(string $section, array $data): string
    {
        return match($section) {
            'executive-summary' => $this->getExecutiveSummaryInsight($data),
            'customer-movement' => $this->getCustomerMovementInsight($data),
            'risk-assessment' => $this->getRiskAssessmentInsight($data),
            'growth-opportunities' => $this->getGrowthOpportunitiesInsight($data),
            'revenue-concentration' => $this->getRevenueConcentrationInsight($data),
            'customer-segments' => $this->getCustomerSegmentsInsight($data),
            'historical-trends' => $this->getHistoricalTrendsInsight($data),
            default => "AI insight for {$section} section - this would be generated by AI analysis of the data.",
        };
    }
    
    private function getExecutiveSummaryInsight(array $data): string
    {
        $revenueChange = $data['analysis']['revenue_change'] ?? 0;
        $customerChange = $data['analysis']['customer_change'] ?? 0;
        $avgRfm = $data['current_period']['average_rfm'] ?? 0;
        $aovChange = $data['analysis']['aov_change'] ?? 0;
        
        // Get additional context for better insights
        $movement = $data['current_period']['customer_movement'] ?? [];
        $retentionRate = $movement['retention_rate'] ?? 0;
        $newCustomers = $movement['new_customers'] ?? 0;
        $lostCustomers = $movement['lost_customers'] ?? 0;
        
        $concentration = $data['concentration'] ?? [];
        $top10Share = $concentration['top_10_share'] ?? 0;
        $customersTo80 = $concentration['customers_to_80_percent'] ?? 0;
        
        // Build comprehensive insight based on multiple factors
        $insight = "";
        
        // Revenue Analysis
        if ($revenueChange > 10) {
            $insight .= "Exceptional revenue growth of {$revenueChange}% demonstrates strong market position and effective business strategies. ";
        } elseif ($revenueChange > 5) {
            $insight .= "Solid revenue growth of {$revenueChange}% indicates healthy business momentum. ";
        } elseif ($revenueChange < -10) {
            $insight .= "Critical revenue decline of {$revenueChange}% requires immediate intervention and strategic review. ";
        } elseif ($revenueChange < -5) {
            $insight .= "Revenue decline of {$revenueChange}% signals potential market challenges requiring attention. ";
        } else {
            $insight .= "Stable revenue performance with {$revenueChange}% change suggests consistent business operations. ";
        }
        
        // Customer Base Analysis
        if ($customerChange > 0) {
            $insight .= "Customer base growth of {$customerChange}% combined with {$retentionRate}% retention rate shows effective customer acquisition and retention strategies. ";
        } else {
            $insight .= "Customer base decline of " . abs($customerChange) . "% with {$lostCustomers} customers lost indicates need for improved retention programs. ";
        }
        
        // RFM Score Analysis
        if ($avgRfm >= 7) {
            $insight .= "High average RFM score of {$avgRfm}/10 indicates strong customer value and engagement levels. ";
        } elseif ($avgRfm >= 4) {
            $insight .= "Moderate RFM score of {$avgRfm}/10 suggests opportunities for customer development and value enhancement. ";
        } else {
            $insight .= "Low RFM score of {$avgRfm}/10 indicates need for customer re-engagement and value-building initiatives. ";
        }
        
        // Revenue Concentration Risk
        if ($top10Share > 70) {
            $insight .= "High revenue concentration risk: top 10 customers represent {$top10Share}% of revenue. ";
        } elseif ($top10Share > 50) {
            $insight .= "Moderate revenue concentration: top 10 customers represent {$top10Share}% of revenue. ";
        }
        
        // Actionable Recommendations
        $insight .= "Immediate actions: " . $this->getActionRecommendations($data);
        
        return $insight;
    }
    
    private function getActionRecommendations(array $data): string
    {
        $revenueChange = $data['analysis']['revenue_change'] ?? 0;
        $customerChange = $data['analysis']['customer_change'] ?? 0;
        $avgRfm = $data['current_period']['average_rfm'] ?? 0;
        $retentionRate = $data['current_period']['customer_movement']['retention_rate'] ?? 0;
        $top10Share = $data['concentration']['top_10_share'] ?? 0;
        
        $recommendations = [];
        
        if ($revenueChange < -5) {
            $recommendations[] = "implement customer retention campaigns";
            $recommendations[] = "review pricing strategies";
        }
        
        if ($customerChange < 0) {
            $recommendations[] = "enhance customer acquisition efforts";
            $recommendations[] = "improve onboarding processes";
        }
        
        if ($avgRfm < 5) {
            $recommendations[] = "develop customer value enhancement programs";
            $recommendations[] = "focus on upselling opportunities";
        }
        
        if ($retentionRate < 80) {
            $recommendations[] = "strengthen customer relationship management";
            $recommendations[] = "implement loyalty programs";
        }
        
        if ($top10Share > 70) {
            $recommendations[] = "diversify customer base to reduce concentration risk";
        }
        
        if (empty($recommendations)) {
            $recommendations[] = "maintain current successful strategies";
            $recommendations[] = "explore expansion opportunities";
        }
        
        return implode(", ", array_slice($recommendations, 0, 3)) . ".";
    }
    
    private function getCustomerMovementInsight(array $data): string
    {
        $movement = $data['current_period']['customer_movement'] ?? [];
        $retained = $movement['retained_customers'] ?? 0;
        $new = $movement['new_customers'] ?? 0;
        $returned = $movement['returned_customers'] ?? 0;
        $lost = $movement['lost_customers'] ?? 0;
        $retentionRate = $movement['retention_rate'] ?? 0;
        
        if ($retentionRate > 80) {
            return "Excellent customer retention rate of {$retentionRate}% with {$retained} retained customers. Strong acquisition with {$new} new customers and {$returned} returning customers. Only {$lost} customers lost, indicating effective retention strategies.";
        } elseif ($retentionRate > 60) {
            return "Good customer retention rate of {$retentionRate}% with {$retained} retained customers. Balanced growth with {$new} new customers and {$returned} returning customers. {$lost} customers lost suggests room for improvement in retention efforts.";
        } else {
            return "Customer retention rate of {$retentionRate}% needs attention with {$retained} retained customers. While {$new} new customers were acquired and {$returned} returned, {$lost} customers were lost. Focus on retention strategies to improve customer loyalty.";
        }
    }
    
    private function getRiskAssessmentInsight(array $data): string
    {
        $risks = $data['risk_analysis'] ?? [];
        $riskCount = count($risks);
        $highRiskCount = count(array_filter($risks, fn($r) => ($r['severity'] ?? '') === 'high'));
        $mediumRiskCount = count(array_filter($risks, fn($r) => ($r['severity'] ?? '') === 'medium'));
        
        // Get additional context
        $concentration = $data['concentration'] ?? [];
        $top10Share = $concentration['top_10_share'] ?? 0;
        $revenueChange = $data['analysis']['revenue_change'] ?? 0;
        $customerChange = $data['analysis']['customer_change'] ?? 0;
        $retentionRate = $data['current_period']['customer_movement']['retention_rate'] ?? 0;
        
        $insight = "";
        
        // Risk severity assessment
        if ($highRiskCount > 0) {
            $insight .= "Critical risk profile: {$highRiskCount} high-severity and {$mediumRiskCount} medium-severity risks require immediate strategic intervention. ";
        } elseif ($riskCount > 0) {
            $insight .= "Moderate risk profile: {$riskCount} identified risks should be proactively managed to prevent escalation. ";
        } else {
            $insight .= "Low risk profile: No significant risks identified, but maintain vigilant monitoring. ";
        }
        
        // Specific risk analysis
        if ($top10Share > 80) {
            $insight .= "Extreme revenue concentration ({$top10Share}% from top 10 customers) represents the highest business continuity risk. ";
        } elseif ($top10Share > 60) {
            $insight .= "High revenue concentration ({$top10Share}% from top 10 customers) creates significant dependency risk. ";
        }
        
        if ($revenueChange < -5 && $customerChange < 0) {
            $insight .= "Concurrent revenue decline ({$revenueChange}%) and customer loss creates compounding risk requiring urgent attention. ";
        }
        
        if ($retentionRate < 80) {
            $insight .= "Low retention rate ({$retentionRate}%) indicates systematic customer satisfaction issues. ";
        }
        
        // Risk mitigation priorities
        $priorities = [];
        if ($top10Share > 70) {
            $priorities[] = "diversify customer portfolio";
        }
        if ($revenueChange < -5) {
            $priorities[] = "implement revenue stabilization measures";
        }
        if ($retentionRate < 80) {
            $priorities[] = "strengthen customer retention programs";
        }
        if ($customerChange < -10) {
            $priorities[] = "accelerate customer acquisition";
        }
        
        if (!empty($priorities)) {
            $insight .= "Immediate priorities: " . implode(", ", array_slice($priorities, 0, 3)) . ". ";
        }
        
        // Early warning system
        $insight .= "Establish monitoring dashboards for key risk indicators and implement quarterly risk reviews to prevent escalation.";
        
        return $insight;
    }
    
    private function getGrowthOpportunitiesInsight(array $data): string
    {
        $opportunities = $data['opportunities'] ?? [];
        $opportunityCount = count($opportunities);
        $highPriorityCount = count(array_filter($opportunities, fn($o) => ($o['priority'] ?? '') === 'high'));
        
        // Get customer segment data
        $segments = $data['segments'] ?? [];
        $highValue = $segments['high_value']['count'] ?? 0;
        $midValue = $segments['mid_value']['count'] ?? 0;
        $lowValue = $segments['low_value']['count'] ?? 0;
        $atRisk = $segments['at_risk']['count'] ?? 0;
        
        // Get business metrics
        $revenueChange = $data['analysis']['revenue_change'] ?? 0;
        $customerChange = $data['analysis']['customer_change'] ?? 0;
        $avgRfm = $data['current_period']['average_rfm'] ?? 0;
        $aov = $data['current_period']['average_order_value'] ?? 0;
        
        $insight = "";
        
        // Opportunity assessment
        if ($highPriorityCount > 0) {
            $insight .= "Exceptional growth potential: {$opportunityCount} opportunities identified with {$highPriorityCount} high-priority areas requiring immediate focus. ";
        } elseif ($opportunityCount > 0) {
            $insight .= "Strong growth foundation: {$opportunityCount} opportunities identified that can drive sustainable business expansion. ";
        } else {
            $insight .= "Growth opportunity development needed: focus on customer base expansion and value enhancement strategies. ";
        }
        
        // Customer portfolio analysis
        if ($midValue > $highValue) {
            $insight .= "Mid-value customer segment ({$midValue} customers) represents the largest growth opportunity for upselling and development. ";
        } elseif ($highValue > 0) {
            $insight .= "High-value customer base ({$highValue} customers) provides strong foundation for expansion and retention strategies. ";
        }
        
        if ($atRisk > 0) {
            $insight .= "At-risk customer segment ({$atRisk} customers) requires immediate retention intervention to prevent revenue loss. ";
        }
        
        // Revenue optimization
        if ($aov < 1000) {
            $insight .= "Average order value of £{$aov} indicates significant upselling potential across customer segments. ";
        }
        
        if ($avgRfm < 6) {
            $insight .= "Average RFM score of {$avgRfm}/10 suggests customer development opportunities to increase overall value. ";
        }
        
        // Strategic priorities
        $priorities = [];
        if ($revenueChange < 0) {
            $priorities[] = "implement revenue stabilization strategies";
        }
        if ($customerChange < 0) {
            $priorities[] = "accelerate customer acquisition programs";
        }
        if ($midValue > 0) {
            $priorities[] = "develop mid-value customer upselling campaigns";
        }
        if ($atRisk > 0) {
            $priorities[] = "launch at-risk customer retention initiatives";
        }
        
        if (!empty($priorities)) {
            $insight .= "Immediate priorities: " . implode(", ", array_slice($priorities, 0, 3)) . ". ";
        }
        
        // Implementation roadmap
        $insight .= "Focus on quick-win opportunities first, then develop long-term strategic initiatives for sustainable growth.";
        
        return $insight;
    }
    

    
    private function callOpenAI(string $prompt): string
    {
        $apiKey = config('ai.openai.api_key');
        $model = config('ai.openai.model', 'gpt-3.5-turbo');
        $maxTokens = config('ai.openai.max_tokens', 500);
        $temperature = config('ai.openai.temperature', 0.7);
        
        if (!$apiKey) {
            throw new \Exception('OpenAI API key not configured');
        }
        
        $headers = [];
        if (config('ai.openai.org')) {
            $headers['OpenAI-Organization'] = config('ai.openai.org');
        }
        if (config('ai.openai.project')) {
            $headers['OpenAI-Project'] = config('ai.openai.project');
        }

        $response = Http::withToken($apiKey)
            ->withHeaders($headers)
            ->timeout(30)
            ->acceptJson()
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ]);

        if ($response->failed()) {
            $status = $response->status();
            $body = $response->json();
            $message = $body['error']['message'] ?? $response->body();
            throw new \Exception("OpenAI request failed ({$status}): {$message}");
        }

        $result = $response->json();
        
        if (!isset($result['choices'][0]['message']['content'])) {
            throw new \Exception('Invalid response from OpenAI API');
        }
        
        return trim($result['choices'][0]['message']['content']);
    }
    
    private function getRevenueConcentrationInsight(array $data): string
    {
        $concentration = $data['concentration'] ?? [];
        $top10Share = $concentration['top_10_share'] ?? 0;
        $top50Share = $concentration['top_50_share'] ?? 0;
        $customersTo80 = $concentration['customers_to_80_percent'] ?? 0;
        
        $insight = "";
        
        // Risk assessment based on concentration
        if ($top10Share > 90) {
            $insight .= "EXTREME revenue concentration: {$top10Share}% from top 10 customers represents critical business vulnerability. ";
        } elseif ($top10Share > 80) {
            $insight .= "CRITICAL revenue concentration: {$top10Share}% from top 10 customers creates substantial business risk. ";
        } elseif ($top10Share > 60) {
            $insight .= "HIGH revenue concentration: {$top10Share}% from top 10 customers indicates significant dependency risk. ";
        } elseif ($top10Share > 40) {
            $insight .= "MODERATE revenue concentration: {$top10Share}% from top 10 customers suggests careful monitoring required. ";
        } else {
            $insight .= "HEALTHY revenue distribution: {$top10Share}% from top 10 customers shows good diversification. ";
        }
        
        // Specific analysis
        if ($customersTo80 <= 5) {
            $insight .= "Only {$customersTo80} customers drive 80% of revenue - extreme concentration requiring immediate diversification. ";
        } elseif ($customersTo80 <= 10) {
            $insight .= "{$customersTo80} customers for 80% revenue indicates high concentration risk needing strategic attention. ";
        }
        
        // Strategic recommendations
        if ($top10Share > 70) {
            $insight .= "Immediate priority: implement customer diversification strategies, develop mid-tier accounts, and establish key account retention programs. ";
        } elseif ($top10Share > 50) {
            $insight .= "Focus on expanding mid-tier customer base and reducing dependency through strategic account development. ";
        }
        
        $insight .= "Monitor key account health indicators and develop contingency plans for potential customer loss.";
        
        return $insight;
    }
    
    private function getCustomerSegmentsInsight(array $data): string
    {
        $segments = $data['segments'] ?? [];
        $highValue = $segments['high_value']['count'] ?? 0;
        $midValue = $segments['mid_value']['count'] ?? 0;
        $lowValue = $segments['low_value']['count'] ?? 0;
        $atRisk = $segments['at_risk']['count'] ?? 0;
        $inactive = $segments['inactive']['count'] ?? 0;
        
        $totalActive = $highValue + $midValue + $lowValue + $atRisk;
        $totalCustomers = $totalActive + $inactive;
        
        $insight = "";
        
        // Portfolio balance analysis
        if ($highValue > $midValue && $highValue > $lowValue) {
            $insight .= "Strong portfolio: {$highValue} high-value customers form the largest active segment, indicating excellent customer quality. ";
        } elseif ($midValue > $highValue && $midValue > $lowValue) {
            $insight .= "Growth opportunity: {$midValue} mid-value customers represent the largest segment with significant upselling potential. ";
        } elseif ($lowValue > $highValue && $lowValue > $midValue) {
            $insight .= "Development focus needed: {$lowValue} low-value customers dominate, requiring strategic value enhancement programs. ";
        }
        
        // Specific segment analysis
        if ($atRisk > 0) {
            $atRiskPct = $totalActive > 0 ? round(($atRisk / $totalActive) * 100, 1) : 0;
            $insight .= "{$atRisk} at-risk customers ({$atRiskPct}% of active base) require immediate retention intervention. ";
        }
        
        if ($inactive > $totalActive) {
            $inactivePct = round(($inactive / $totalCustomers) * 100, 1);
            $insight .= "Concerning inactive base: {$inactive} inactive customers ({$inactivePct}% of total) suggest need for re-engagement campaigns or database cleanup. ";
        }
        
        // Strategic recommendations
        $priorities = [];
        if ($midValue > $highValue) {
            $priorities[] = "develop mid-value customer upselling programs";
        }
        if ($atRisk > 0) {
            $priorities[] = "implement at-risk customer retention strategies";
        }
        if ($lowValue > $midValue) {
            $priorities[] = "create low-value customer development pathways";
        }
        
        if (!empty($priorities)) {
            $insight .= "Key priorities: " . implode(", ", array_slice($priorities, 0, 2)) . ". ";
        }
        
        $insight .= "Focus on segment migration strategies to move customers up the value ladder systematically.";
        
        return $insight;
    }
    
    private function getHistoricalTrendsInsight(array $data): string
    {
        $trends = $data['historical_trends'] ?? [];
        
        if (empty($trends)) {
            return "No historical trends data available for analysis. Consider implementing regular performance tracking to identify patterns and inform strategic decisions.";
        }
        
        $trendCount = count($trends);
        $firstPeriod = reset($trends);
        $lastPeriod = end($trends);
        
        $firstCustomers = $firstPeriod['total_customers'] ?? 0;
        $lastCustomers = $lastPeriod['total_customers'] ?? 0;
        $firstRfm = $firstPeriod['average_rfm'] ?? 0;
        $lastRfm = $lastPeriod['average_rfm'] ?? 0;
        $firstHighValue = $firstPeriod['high_value_customers'] ?? 0;
        $lastHighValue = $lastPeriod['high_value_customers'] ?? 0;
        
        // Calculate changes
        $customerChange = $firstCustomers > 0 ? round((($lastCustomers - $firstCustomers) / $firstCustomers) * 100, 1) : 0;
        $rfmChange = $firstRfm > 0 ? round((($lastRfm - $firstRfm) / $firstRfm) * 100, 1) : 0;
        $highValueChange = $lastHighValue - $firstHighValue;
        
        $insight = "";
        
        // Overall trajectory
        $insight .= "Over {$trendCount} periods analyzed: ";
        
        // Customer base trend
        if ($customerChange > 10) {
            $insight .= "strong customer growth ({$customerChange}%) indicating successful acquisition strategies. ";
        } elseif ($customerChange > 2) {
            $insight .= "positive customer growth ({$customerChange}%) showing healthy expansion. ";
        } elseif ($customerChange > -2) {
            $insight .= "stable customer base ({$customerChange}% change) with consistent retention. ";
        } elseif ($customerChange > -10) {
            $insight .= "customer base decline ({$customerChange}%) requiring attention to acquisition and retention. ";
        } else {
            $insight .= "significant customer loss ({$customerChange}%) indicating urgent need for strategic intervention. ";
        }
        
        // Quality trend
        if ($rfmChange > 10) {
            $insight .= "Excellent RFM improvement ({$rfmChange}%) demonstrates effective customer value enhancement. ";
        } elseif ($rfmChange > 5) {
            $insight .= "Positive RFM trend ({$rfmChange}%) shows improving customer quality. ";
        } elseif ($rfmChange > -5) {
            $insight .= "Stable RFM performance ({$rfmChange}% change) maintaining customer value levels. ";
        } else {
            $insight .= "Declining RFM quality ({$rfmChange}%) suggests need for customer development programs. ";
        }
        
        // High-value customer trend
        if ($highValueChange > 0) {
            $insight .= "High-value customer count increased by {$highValueChange}, indicating successful customer development. ";
        } elseif ($highValueChange == 0) {
            $insight .= "High-value customer count remained stable, suggesting consistent premium customer retention. ";
        } else {
            $insight .= "High-value customer count decreased by " . abs($highValueChange) . ", requiring focus on premium customer retention and development. ";
        }
        
        // Future outlook
        if ($customerChange > 0 && $rfmChange > 0) {
            $insight .= "Positive momentum in both growth and quality suggests continued strong performance trajectory.";
        } elseif ($customerChange > 0 || $rfmChange > 0) {
            $insight .= "Mixed signals require balanced focus on both customer acquisition and value enhancement.";
        } else {
            $insight .= "Concerning trends indicate need for comprehensive strategic review and intervention.";
        }
        
        return $insight;
    }
}
