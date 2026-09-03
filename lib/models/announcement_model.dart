class AnnouncementModel {
  final int id;
  final String title;
  final String content;
  final bool isUrgent;
  final DateTime createdAt;

  AnnouncementModel({
    required this.id,
    required this.title,
    required this.content,
    this.isUrgent = false,
    required this.createdAt,
  });

  factory AnnouncementModel.fromJson(Map<String, dynamic> json) {
    bool parseBool(dynamic val) {
      if (val is bool) return val;
      if (val is int) return val == 1;
      if (val is String) return val == '1' || val.toLowerCase() == 'true';
      return false;
    }

    return AnnouncementModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      title: json['title']?.toString() ?? '',
      content: json['content']?.toString() ?? '',
      isUrgent: parseBool(json['isUrgent'] ?? json['is_urgent']),
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'].toString())
          : DateTime.now(),
    );
  }
}
