import 'package:flutter/material.dart';

class MatchsScreen extends StatelessWidget {
  const MatchsScreen({super.key});

  static const Color greenPrimary = Color(0xFF1E5235);
  static const Color goldAccent = Color(0xFFD4AF37);

  @override
  Widget build(BuildContext context) {
    // Liste factice de matchs
    final List<Map<String, String>> matchs = [
      {
        'equipeA': 'Équipe A',
        'equipeB': 'Équipe B',
        'heure': '15:00',
        'statut': 'À venir',
      },
      {
        'equipeA': 'Équipe C',
        'equipeB': 'Équipe D',
        'heure': '17:30',
        'statut': 'En cours',
      },
    ];

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Gestion des Matchs',
          style: TextStyle(color: Colors.white),
        ),
        backgroundColor: greenPrimary,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: ListView.builder(
        padding: const EdgeInsets.all(16.0),
        itemCount: matchs.length,
        itemBuilder: (context, index) {
          final match = matchs[index];
          return Card(
            elevation: 2,
            margin: const EdgeInsets.only(bottom: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
              side: const BorderSide(color: goldAccent, width: 1),
            ),
            child: ListTile(
              contentPadding: const EdgeInsets.all(16),
              leading: const CircleAvatar(
                backgroundColor: greenPrimary,
                child: Icon(Icons.sports, color: goldAccent),
              ),
              title: Text(
                '${match['equipeA']} vs ${match['equipeB']}',
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                ),
              ),
              subtitle: Padding(
                padding: const EdgeInsets.only(top: 8.0),
                child: Text('Heure : ${match['heure']}'),
              ),
              trailing: Chip(
                label: Text(
                  match['statut']!,
                  style: const TextStyle(color: Colors.white, fontSize: 12),
                ),
                backgroundColor: match['statut'] == 'En cours'
                    ? Colors.orange
                    : greenPrimary,
              ),
            ),
          );
        },
      ),
    );
  }
}
