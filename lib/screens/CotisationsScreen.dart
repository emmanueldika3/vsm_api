import 'package:flutter/material.dart';

class CotisationsScreen extends StatelessWidget {
  const CotisationsScreen({super.key});

  static const Color greenPrimary = Color(0xFF1E5235);
  static const Color goldAccent = Color(0xFFD4AF37);

  @override
  Widget build(BuildContext context) {
    // Liste factice de cotisations
    final List<Map<String, dynamic>> cotisations = [
      {
        'membre': 'Jean Dupont',
        'montant': '15 000 XAF',
        'date': '12 Août 2026',
        'paye': true,
      },
      {
        'membre': 'Marie Claire',
        'montant': '15 000 XAF',
        'date': '15 Août 2026',
        'paye': true,
      },
      {
        'membre': 'Paul Biya Jr',
        'montant': '15 000 XAF',
        'date': 'En attente',
        'paye': false,
      },
    ];

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Gestion des Cotisations',
          style: TextStyle(color: Colors.white),
        ),
        backgroundColor: greenPrimary,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: ListView.builder(
        padding: const EdgeInsets.all(16.0),
        itemCount: cotisations.length,
        itemBuilder: (context, index) {
          final item = cotisations[index];
          final bool estPaye = item['paye'];

          return Card(
            elevation: 2,
            margin: const EdgeInsets.only(bottom: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
              side: BorderSide(
                color: estPaye ? goldAccent : Colors.grey.shade300,
                width: estPaye ? 1.5 : 1.0,
              ),
            ),
            child: ListTile(
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 16,
                vertical: 8,
              ),
              leading: CircleAvatar(
                backgroundColor: estPaye ? greenPrimary : Colors.grey,
                child: Icon(
                  estPaye ? Icons.check : Icons.hourglass_empty,
                  color: goldAccent,
                ),
              ),
              title: Text(
                item['membre'],
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                ),
              ),
              subtitle: Padding(
                padding: const EdgeInsets.only(top: 4.0),
                child: Text('Montant : ${item['montant']} • ${item['date']}'),
              ),
              trailing: Chip(
                label: Text(
                  estPaye ? 'Payé' : 'En attente',
                  style: const TextStyle(color: Colors.white, fontSize: 12),
                ),
                backgroundColor: estPaye ? greenPrimary : Colors.orange,
              ),
            ),
          );
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          // Action pour ajouter une nouvelle cotisations
        },
        backgroundColor: goldAccent,
        child: const Icon(Icons.add, color: Colors.white),
      ),
    );
  }
}
