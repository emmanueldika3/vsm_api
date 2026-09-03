import 'package:flutter/material.dart';

class GalerieScreen extends StatelessWidget {
  const GalerieScreen({super.key});

  static const Color greenPrimary = Color(0xFF1E5235);
  static const Color goldAccent = Color(0xFFD4AF37);

  @override
  Widget build(BuildContext context) {
    // Liste factice d'images ou d'événements pour la galerie
    final List<Map<String, String>> photos = [
      {'titre': 'Match VSM vs PK11', 'date': '20 Août 2026'},
      {'titre': 'Réunion du bureau', 'date': '15 Août 2026'},
      {'titre': 'Tournoi inter-quartiers', 'date': '10 Août 2026'},
      {'titre': 'Remise des trophées', 'date': '05 Août 2026'},
      {'titre': 'Entraînement officiel', 'date': '01 Août 2026'},
      {'titre': 'Moment de détente', 'date': '28 Jui 2026'},
    ];

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Galerie Photos',
          style: TextStyle(color: Colors.white),
        ),
        backgroundColor: greenPrimary,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: Padding(
        padding: const EdgeInsets.all(12.0),
        child: GridView.builder(
          itemCount: photos.length,
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2, // 2 colonnes
            crossAxisSpacing: 12.0,
            mainAxisSpacing: 12.0,
            childAspectRatio: 0.85, // Proportion de la carte
          ),
          itemBuilder: (context, index) {
            final photo = photos[index];
            return Card(
              elevation: 3,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
                side: const BorderSide(color: goldAccent, width: 1),
              ),
              clipBehavior: Clip.antiAlias,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Zone image (Placeholder ou Image réseau/locale)
                  Expanded(
                    child: Container(
                      color: greenPrimary.withOpacity(0.1),
                      child: const Icon(
                        Icons.image,
                        size: 50,
                        color: greenPrimary,
                      ),
                    ),
                  ),
                  // Légende de la photo
                  Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          photo['titre']!,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 14,
                            color: greenPrimary,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          photo['date']!,
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade600,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            );
          },
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          // Action pour ajouter une nouvelle photo
        },
        backgroundColor: goldAccent,
        child: const Icon(Icons.add_a_photo, color: Colors.white),
      ),
    );
  }
}
