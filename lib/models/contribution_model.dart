class ContributionModel {
  final int id;
  final String title;
  final double amount;
  final String status; // 'paid', 'pending', 'late'
  final DateTime dueDate;
  final DateTime? paidAt;
  final String? description;

  ContributionModel({
    required this.id,
    required this.title,
    required this.amount,
    required this.status,
    required this.dueDate,
    this.paidAt,
    this.description,
  });

  factory ContributionModel.fromJson(Map<String, dynamic> json) {
    return ContributionModel(
      id: json['id'] as int,
      title: json['title'] as String,
      amount: (json['amount'] as num).toDouble(),
      status: (json['status'] as String?) ?? 'pending',
      dueDate: DateTime.parse(json['due_date'] as String),
      paidAt: json['paid_at'] != null
          ? DateTime.parse(json['paid_at'] as String)
          : null,
      description: json['description'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'amount': amount,
      'status': status,
      'due_date': dueDate.toIso8601String(),
      'paid_at': paidAt?.toIso8601String(),
      'description': description,
    };
  }

  // Helpers de statut de paiement
  bool get isPaid => status == 'paid';
  bool get isPending => status == 'pending';
  bool get isLate => status == 'late';
}
