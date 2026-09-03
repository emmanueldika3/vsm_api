class EventModel {
  final int id;
  final String title;
  final String type; // 'training' ou 'match'
  final DateTime date;
  final String location;
  final String? description;

  EventModel({
    required this.id,
    required this.title,
    required this.type,
    required this.date,
    required this.location,
    this.description,
  });

  factory EventModel.fromJson(Map<String, dynamic> json) {
    return EventModel(
      id: json['id'],
      title: json['title'],
      type: json['type'],
      date: DateTime.parse(json['date']),
      location: json['location'],
      description: json['description'],
    );
  }
}
