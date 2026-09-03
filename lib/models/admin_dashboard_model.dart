class AdminDashboardData {
  final int activeMembers;
  final int pendingMembersCount;
  final double clubBalance;
  final double contributionRate;
  final List<PendingMember> pendingMembers;
  final FinancialSummary financialSummary;

  AdminDashboardData({
    required this.activeMembers,
    required this.pendingMembersCount,
    required this.clubBalance,
    required this.contributionRate,
    required this.pendingMembers,
    required this.financialSummary,
  });

  factory AdminDashboardData.fromJson(Map<String, dynamic> json) {
    return AdminDashboardData(
      activeMembers: json['active_members'] ?? json['activeMembers'] ?? 0,
      pendingMembersCount:
          json['pending_members_count'] ?? json['pendingMembersCount'] ?? 0,
      clubBalance: (json['club_balance'] ?? json['clubBalance'] ?? 0)
          .toDouble(),
      contributionRate:
          (json['contribution_rate'] ?? json['contributionRate'] ?? 0)
              .toDouble(),
      pendingMembers:
          (json['pending_members'] as List? ??
                  json['pendingMembers'] as List? ??
                  [])
              .map((m) => PendingMember.fromJson(m as Map<String, dynamic>))
              .toList(),
      financialSummary: FinancialSummary.fromJson(
        json['financial_summary'] ?? json['financialSummary'] ?? {},
      ),
    );
  }
}

class PendingMember {
  final int id;
  final String name;
  final String position;
  final String createdAt;

  PendingMember({
    required this.id,
    required this.name,
    required this.position,
    required this.createdAt,
  });

  factory PendingMember.fromJson(Map<String, dynamic> json) {
    return PendingMember(
      id: json['id'] ?? 0,
      name: json['name'] ?? json['full_name'] ?? 'Nom inconnu',
      position: json['position'] ?? json['role'] ?? 'Membre',
      createdAt: json['created_at'] ?? json['createdAt'] ?? '',
    );
  }
}

class FinancialSummary {
  final int paidCount;
  final int totalCount;
  final double totalIncome;
  final double totalExpenses;

  FinancialSummary({
    required this.paidCount,
    required this.totalCount,
    required this.totalIncome,
    required this.totalExpenses,
  });

  factory FinancialSummary.fromJson(Map<String, dynamic> json) {
    return FinancialSummary(
      paidCount: json['paid_count'] ?? json['paidCount'] ?? 0,
      totalCount: json['total_count'] ?? json['totalCount'] ?? 0,
      totalIncome: (json['total_income'] ?? json['totalIncome'] ?? 0)
          .toDouble(),
      totalExpenses: (json['total_expenses'] ?? json['totalExpenses'] ?? 0)
          .toDouble(),
    );
  }
}
