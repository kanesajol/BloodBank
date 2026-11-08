import { useState } from 'react';
import { Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { registerDonor } from '../api/client';

const BLOOD_TYPES = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];

export default function RegisterDonorScreen({ navigation }) {
  const [fullName, setFullName] = useState('');
  const [bloodType, setBloodType] = useState('O+');
  const [age, setAge] = useState('');
  const [gender, setGender] = useState('Male');
  const [contact, setContact] = useState('');
  const [barangay, setBarangay] = useState('');
  const [municipality, setMunicipality] = useState('');
  const [province, setProvince] = useState('Surigao del Norte');
  const [date, setDate] = useState(new Date().toISOString().slice(0, 10));
  const [location, setLocation] = useState('Caraga Regional Hospital');
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState('');

  return (
    <ScrollView style={styles.root} contentContainerStyle={{ padding: 16 }}>
      <Text style={styles.label}>Full name</Text>
      <TextInput style={styles.input} value={fullName} onChangeText={setFullName} />
      <Text style={styles.label}>Blood type</Text>
      <View style={styles.chips}>
        {BLOOD_TYPES.map((type) => (
          <Pressable
            key={type}
            style={[styles.chip, bloodType === type && styles.chipActive]}
            onPress={() => setBloodType(type)}
          >
            <Text style={bloodType === type ? styles.chipTextActive : styles.chipText}>{type}</Text>
          </Pressable>
        ))}
      </View>
      <Text style={styles.label}>Age</Text>
      <TextInput style={styles.input} value={age} onChangeText={setAge} keyboardType="number-pad" />
      <Text style={styles.label}>Gender</Text>
      <View style={styles.row}>
        {['Male', 'Female'].map((g) => (
          <Pressable key={g} style={[styles.chip, gender === g && styles.chipActive]} onPress={() => setGender(g)}>
            <Text style={gender === g ? styles.chipTextActive : styles.chipText}>{g}</Text>
          </Pressable>
        ))}
      </View>
      <Text style={styles.label}>Contact</Text>
      <TextInput style={styles.input} value={contact} onChangeText={setContact} keyboardType="phone-pad" />
      <Text style={styles.label}>Barangay</Text>
      <TextInput style={styles.input} value={barangay} onChangeText={setBarangay} />
      <Text style={styles.label}>Municipality</Text>
      <TextInput style={styles.input} value={municipality} onChangeText={setMunicipality} />
      <Text style={styles.label}>Province</Text>
      <TextInput style={styles.input} value={province} onChangeText={setProvince} />
      <Text style={styles.label}>First donation date</Text>
      <TextInput style={styles.input} value={date} onChangeText={setDate} />
      <Text style={styles.label}>Collection location</Text>
      <TextInput style={styles.input} value={location} onChangeText={setLocation} />
      <Pressable
        style={styles.button}
        onPress={async () => {
          setSaving(true);
          setMessage('');
          try {
            const data = await registerDonor({
              full_name: fullName.trim(),
              blood_type: bloodType,
              age: parseInt(age, 10),
              gender,
              contact: contact.trim(),
              barangay: barangay.trim(),
              municipality: municipality.trim(),
              province: province.trim(),
              initial_donation_date: date,
              donation_location: location.trim(),
            });
            setMessage(data.message);
            navigation.replace('DonorDetail', { donorId: data.donor.id, donorName: data.donor.name });
          } catch (e) {
            setMessage(e.message);
          } finally {
            setSaving(false);
          }
        }}
      >
        <Text style={styles.buttonText}>{saving ? 'Saving...' : 'Save donor'}</Text>
      </Pressable>
      {message ? <Text style={styles.message}>{message}</Text> : null}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff' },
  label: { fontWeight: '600', marginTop: 10, marginBottom: 4, color: '#334155' },
  input: { borderWidth: 1, borderColor: '#e2e8f0', borderRadius: 10, padding: 10 },
  row: { flexDirection: 'row', gap: 8 },
  chips: { flexDirection: 'row', flexWrap: 'wrap', gap: 6 },
  chip: { borderWidth: 1, borderColor: '#e2e8f0', borderRadius: 8, paddingHorizontal: 10, paddingVertical: 6 },
  chipActive: { backgroundColor: '#990000', borderColor: '#990000' },
  chipText: { color: '#334155' },
  chipTextActive: { color: '#fff', fontWeight: '700' },
  button: { backgroundColor: '#990000', borderRadius: 10, padding: 14, alignItems: 'center', marginTop: 20 },
  buttonText: { color: '#fff', fontWeight: '700' },
  message: { marginTop: 12, color: '#475569' },
});
