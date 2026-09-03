import 'package:flutter/material.dart';

class AnnuaireScreen extends StatelessWidget {
  const AnnuaireScreen({super.key});

  static const Color greenPrimary = Color(0xFF1E5235);
  static const Color goldAccent = Color(0xFFD4AF37);

  @override
  Widget build(BuildContext context) {
    // Liste factice des membres de l'annuaire
    final List<Map<String, String>> membres = [
      {
        'nom': 'Jean Dupont',
        'role': 'Président',
        'telephone': '+237 699 00 00 01',
        'initiales': 'JD',
      },
      {
        'nom': 'Marie Claire',
        'role': 'Trésorière',
        'telephone': '+237 677 00 00 02',
        'initiales': 'MC',
      },
      {
        'nom': 'Paul Biya Jr',
        'role': 'Capitaine d\'équipe',
        'telephone': '+237 655 00 00 03',
        'initiales': 'PB',
      },
      {
        'nom': 'Sophie Martin',
        'role': 'Membre actif',
        'telephone': '+237 622 00 00 04',
        'initiales': 'SM',
      },
    ];

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Annuaire des Membres',
          style: TextStyle(color: Colors.white),
        ),
        backgroundColor: greenPrimary,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: ListView.builder(
        padding: const EdgeInsets.all(16.0),
        itemCount: membres.length,
        itemBuilder: (context, index) {
          final membre = membres[index];

          return Card(
            elevation: 2,
            margin: const EdgeInsets.only(bottom: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
              side: const BorderSide(color: goldAccent, width: 1),
            ),
            child: ListTile(
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 16,
                vertical: 8,
              ),
              leading: CircleAvatar(
                backgroundColor: greenPrimary,
                child: Text(
                  membre['initiales']!,
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              title: Text(
                membre['nom']!,
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                ),
              ),
              subtitle: Padding(
                padding: const EdgeInsets.only(top: 4.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      membre['role']!,
                      style: const TextStyle(
                        color: goldAccent,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      membre['telephone']!,
                      style: TextStyle(color: Colors.grey.shade700),
                    ),
                  ],
                ),
              ),
              trailing: IconButton(
                icon: const Icon(Icons.phone, color: greenPrimary),
                onPressed: () {
                  // Action pour appeler ou contacter le membre
                },
              ),
            ),
          );
        },
      ),
    );
  }
}
